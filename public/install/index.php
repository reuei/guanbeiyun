<?php
/**
 * 管备云备案系统 - 安装程序
 */
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Shanghai');

define('GB_ROOT', dirname(__DIR__, 2));
define('GB_INSTALL', __DIR__);

// 已安装则跳转
if (is_file(GB_ROOT . '/config/config.php')) {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    header('Location: ' . str_replace('/install', '', $base) . '/');
    exit;
}

$step = $_GET['step'] ?? '1';
$step = max(1, min(5, (int)$step));
$errors = [];

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** 检测环境 */
function check_env() {
    $items = [];
    $items[] = ['name' => 'PHP 版本 >= 7.4', 'ok' => version_compare(PHP_VERSION, '7.4.0', '>='), 'info' => PHP_VERSION];
    $items[] = ['name' => 'PDO 扩展', 'ok' => extension_loaded('pdo'), 'info' => ''];
    $items[] = ['name' => 'PDO MySQL 驱动', 'ok' => extension_loaded('pdo_mysql'), 'info' => ''];
    $items[] = ['name' => 'mbstring 扩展', 'ok' => extension_loaded('mbstring'), 'info' => ''];
    $items[] = ['name' => 'cURL 扩展', 'ok' => extension_loaded('curl'), 'info' => ''];
    $items[] = ['name' => 'GD 扩展', 'ok' => extension_loaded('gd'), 'info' => ''];
    $items[] = ['name' => 'config 目录可写', 'ok' => is_writable(GB_ROOT . '/config'), 'info' => GB_ROOT . '/config'];
    $items[] = ['name' => 'public/uploads 可写', 'ok' => is_writable(GB_ROOT . '/public/uploads') || @mkdir(GB_ROOT . '/public/uploads', 0755, true), 'info' => GB_ROOT . '/public/uploads'];
    return $items;
}

/** 测试数据库连接 */
function test_db($host, $port, $name, $user, $pass) {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    return $pdo;
}

/** 执行 SQL 文件 */
function import_sql($pdo, $sqlFile, $prefix) {
    $sql = file_get_contents($sqlFile);
    // 替换表前缀
    $sql = str_replace('`gb_', '`' . $prefix, $sql);
    // 统一换行
    $sql = str_replace(["\r\n", "\r"], "\n", $sql);
    // 移除注释行 (-- 开头的行)
    $lines = explode("\n", $sql);
    $lines = array_filter($lines, function ($l) {
        $t = ltrim($l);
        return $t !== '' && strpos($t, '--') !== 0;
    });
    $sql = implode("\n", $lines);
    // 按分号拆分并逐条执行
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '') continue;
        $pdo->exec($stmt);
    }
}

/** 写配置文件 */
function write_config($data) {
    $cfg = [
        'database' => [
            'host' => $data['db_host'],
            'port' => (int)$data['db_port'],
            'name' => $data['db_name'],
            'user' => $data['db_user'],
            'pass' => $data['db_pass'],
            'charset' => 'utf8mb4',
            'prefix' => $data['db_prefix'],
        ],
        'site' => [
            'name' => '管备云备案系统',
            'url' => '',
            'install_time' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
        ],
        'session' => ['prefix' => 'gb_', 'lifetime' => 7200],
        'upload' => [
            'path' => GB_ROOT . '/public/uploads',
            'max_size' => 5242880,
            'allow' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
        ],
        'app' => ['debug' => false],
    ];
    $content = "<?php\n/** 由安装程序生成于 " . date('Y-m-d H:i:s') . " */\nreturn " . var_export($cfg, true) . ";\n";
    file_put_contents(GB_ROOT . '/config/config.php', $content);
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'db') {
        try {
            $pdo = test_db($_POST['db_host'], $_POST['db_port'], $_POST['db_name'], $_POST['db_user'], $_POST['db_pass']);
            // 建库(如果不存在) - 这里假设库已存在
            import_sql($pdo, GB_INSTALL . '/database.sql', $_POST['db_prefix'] ?: 'gb_');
            // 存到 session
            $_SESSION['install_db'] = $_POST;
            redirect_step(4);
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    } elseif ($action === 'admin') {
        try {
            $db = $_SESSION['install_db'] ?? null;
            if (!$db) redirect_step(3);
            $pdo = test_db($db['db_host'], $db['db_port'], $db['db_name'], $db['db_user'], $db['db_pass']);
            $prefix = $db['db_prefix'] ?: 'gb_';
            // 写入管理员
            $adminUser = trim($_POST['admin_user']);
            $adminPass = $_POST['admin_pass'];
            $adminEmail = trim($_POST['admin_email']);
            if (strlen($adminUser) < 3) throw new Exception('管理员用户名至少3位');
            if (strlen($adminPass) < 6) throw new Exception('管理员密码至少6位');
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO {$prefix}admins (username, password, email, role, created_at) VALUES (?, ?, ?, 'super', ?)");
            $stmt->execute([$adminUser, $hash, $adminEmail, $now]);
            // 写配置文件
            write_config($db);
            // 标记安装完成
            file_put_contents(GB_ROOT . '/config/install.lock', $now);
            // 清理
            unset($_SESSION['install_db']);
            redirect_step(5);
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

function redirect_step($n) {
    header('Location: index.php?step=' . $n);
    exit;
}

$stepNames = ['安装协议', '条件检测', '数据库配置', '管理员配置', '安装完成'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>安装向导 - 管备云备案系统</title>
<link rel="stylesheet" href="../assets/css/theme.css">
<link rel="stylesheet" href="../assets/css/site.css">
</head>
<body>
<div class="install-wrap">
  <div class="install-card">
    <div class="install-head">
      <div class="logo">
        <div class="ic">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <h1>管备云备案系统</h1>
      </div>
      <p>专业 ICP 备案服务管理平台 · 安装向导 v1.0.0</p>
    </div>
    <div class="install-body">
      <!-- 步骤指示 -->
      <div class="install-steps">
        <?php for ($i = 1; $i <= 5; $i++):
          $cls = $i < $step ? 'done' : ($i === $step ? 'active' : '');
          $lineCls = $i < $step ? 'done' : '';
        ?>
          <div class="istep <?php echo $cls; ?>">
            <div class="num"><?php if ($i < $step): ?>✓<?php else: echo $i; endif; ?></div>
            <span><?php echo $stepNames[$i-1]; ?></span>
          </div>
          <?php if ($i < 5): ?>
            <div class="line <?php echo $lineCls; ?>"></div>
          <?php endif; ?>
        <?php endfor; ?>
      </div>

      <?php if ($errors): ?>
        <div style="background:rgba(255,77,79,0.08);border:1px solid rgba(255,77,79,0.3);border-radius:6px;padding:12px 16px;margin-bottom:18px;color:var(--danger);font-size:13px;">
          <?php foreach ($errors as $e): ?><div>• <?php echo h($e); ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Step 1: 协议 -->
      <?php if ($step == 1): ?>
        <h3 style="font-size:16px;margin-bottom:12px;">安装许可协议</h3>
        <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:16px;max-height:260px;overflow-y:auto;font-size:13px;line-height:1.9;color:var(--text-2);">
          <p><b>管备云备案系统 最终用户授权协议</b></p>
          <p>欢迎使用管备云备案系统（以下简称"本系统"）。在安装和使用本系统前，请您仔细阅读本协议。一旦您安装、复制或以其他方式使用本系统，即表示您已同意接受本协议各项条款的约束。</p>
          <p><b>1. 授权范围</b></p>
          <p>本系统授权用户在遵守本协议的前提下，用于合法的 ICP 备案信息管理业务。用户不得将本系统用于任何违反法律法规的用途。</p>
          <p><b>2. 使用限制</b></p>
          <p>未经授权，不得对本系统进行反向工程、反编译或反汇编。不得删除或篡改系统中的版权信息与技术支持标识。</p>
          <p><b>3. 免责声明</b></p>
          <p>本系统按"现状"提供，开发者不对系统的适用性、稳定性作任何保证。因使用本系统产生的任何直接或间接损失，开发者不承担责任。</p>
          <p><b>4. 知识产权</b></p>
          <p>本系统的著作权及其他知识产权归开发者所有。用户不得擅自复制、传播、出售本系统。</p>
          <p><b>5. 技术支持</b></p>
          <p>本系统由森企动力提供网站建设与技术支持。</p>
        </div>
        <div style="margin:16px 0;display:flex;align-items:center;gap:8px;">
          <input type="checkbox" id="agree" style="width:16px;height:16px;">
          <label for="agree" style="font-size:13px;cursor:pointer;">我已阅读并同意安装许可协议</label>
        </div>
        <div style="text-align:right;">
          <button class="btn btn-primary" onclick="if(document.getElementById('agree').checked){location.href='index.php?step=2';}else{alert('请先同意安装协议');}">下一步</button>
        </div>

      <!-- Step 2: 条件检测 -->
      <?php elseif ($step == 2):
        $env = check_env();
        $allOk = true;
        foreach ($env as $it) if (!$it['ok']) $allOk = false;
      ?>
        <h3 style="font-size:16px;margin-bottom:12px;">环境条件检测</h3>
        <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:6px;padding:4px 18px;">
          <?php foreach ($env as $it): ?>
            <div class="env-item">
              <div class="name"><?php echo $it['name']; ?><?php if ($it['info']): ?><span class="text-muted text-sm">(<?php echo h($it['info']); ?>)</span><?php endif; ?></div>
              <div class="res"><?php echo $it['ok'] ? '<span class="env-ok">✓ 通过</span>' : '<span class="env-no">✗ 未通过</span>'; ?></div>
            </div>
          <?php endforeach; ?>
        </div>
        <div style="margin-top:18px;display:flex;justify-content:space-between;">
          <button class="btn" onclick="location.href='index.php?step=1'">上一步</button>
          <button class="btn btn-primary" onclick="location.href='index.php?step=3'"><?php echo $allOk ? '下一步' : '仍要继续'; ?></button>
        </div>

      <!-- Step 3: 数据库配置 -->
      <?php elseif ($step == 3): ?>
        <h3 style="font-size:16px;margin-bottom:12px;">数据库配置</h3>
        <form method="post">
          <input type="hidden" name="action" value="db">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="form-group">
              <label class="form-label">数据库主机 <span class="req">*</span></label>
              <input class="form-control" name="db_host" value="127.0.0.1" required>
            </div>
            <div class="form-group">
              <label class="form-label">数据库端口 <span class="req">*</span></label>
              <input class="form-control" name="db_port" value="3306" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">数据库名 <span class="req">*</span></label>
            <input class="form-control" name="db_name" placeholder="如 guanbeiyun" required>
            <div class="form-hint">数据库需已创建并具有访问权限</div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="form-group">
              <label class="form-label">数据库用户名 <span class="req">*</span></label>
              <input class="form-control" name="db_user" value="root" required>
            </div>
            <div class="form-group">
              <label class="form-label">数据库密码</label>
              <input class="form-control" type="password" name="db_pass" value="">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">表前缀 <span class="req">*</span></label>
            <input class="form-control" name="db_prefix" value="gb_" required>
            <div class="form-hint">默认前缀 gb_ ，如需修改请保留下划线结尾</div>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <button type="button" class="btn" onclick="location.href='index.php?step=2'">上一步</button>
            <button type="submit" class="btn btn-primary">测试并导入数据库</button>
          </div>
        </form>

      <!-- Step 4: 管理员配置 -->
      <?php elseif ($step == 4): ?>
        <h3 style="font-size:16px;margin-bottom:12px;">管理员账号配置</h3>
        <div style="background:rgba(0,185,107,0.08);border:1px solid rgba(0,185,107,0.3);border-radius:6px;padding:10px 16px;margin-bottom:18px;color:var(--success);font-size:13px;">✓ 数据库连接并导入成功，请配置超级管理员账号</div>
        <form method="post">
          <input type="hidden" name="action" value="admin">
          <div class="form-group">
            <label class="form-label">管理员用户名 <span class="req">*</span></label>
            <input class="form-control" name="admin_user" value="admin" required minlength="3">
          </div>
          <div class="form-group">
            <label class="form-label">管理员密码 <span class="req">*</span></label>
            <input class="form-control" type="password" name="admin_pass" required minlength="6">
            <div class="form-hint">至少6位字符</div>
          </div>
          <div class="form-group">
            <label class="form-label">管理员邮箱</label>
            <input class="form-control" type="email" name="admin_email" value="">
          </div>
          <div style="display:flex;justify-content:space-between;">
            <button type="button" class="btn" onclick="location.href='index.php?step=3'">上一步</button>
            <button type="submit" class="btn btn-primary">完成安装</button>
          </div>
        </form>

      <!-- Step 5: 完成 -->
      <?php elseif ($step == 5): ?>
        <div style="text-align:center;padding:30px 0;">
          <div style="width:72px;height:72px;margin:0 auto 18px;border-radius:50%;background:var(--success);display:flex;align-items:center;justify-content:center;">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
          </div>
          <h3 style="font-size:22px;margin-bottom:8px;">安装完成！</h3>
          <p class="text-muted" style="margin-bottom:8px;">管备云备案系统已成功安装，欢迎使用。</p>
          <p class="text-muted text-sm" style="margin-bottom:26px;">为安全起见，请删除 install 目录或将其重命名。</p>
          <div style="display:flex;gap:12px;justify-content:center;">
            <a class="btn btn-primary" href="../">访问首页</a>
            <a class="btn" href="../admin/login">进入后台</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
