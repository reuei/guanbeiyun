<?php
/**
 * 管备云备案系统 - 安装向导 v3.0.0
 */
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Shanghai');

define('GB_ROOT', dirname(__DIR__, 2));
define('GB_INSTALL', __DIR__);
define('GB_VERSION', 'v3.0.0');

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
            'version' => '1.0.2',
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

function redirect_step($n) {
    header('Location: index.php?step=' . $n);
    exit;
}

// AJAX: 测试数据库连接 (不导入数据)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'test_db') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        test_db($_POST['db_host'] ?? '', $_POST['db_port'] ?? 3306, $_POST['db_name'] ?? '', $_POST['db_user'] ?? '', $_POST['db_pass'] ?? '');
        echo json_encode(['ok' => true, 'msg' => '数据库连接成功'], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
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
<style>
/* ===== 安装向导专属样式 (覆盖 site.css 默认) ===== */
.install-wrap {
    min-height: 100vh;
    background: linear-gradient(135deg, #e6f4ff 0%, #f5f7fa 50%, #fff 100%);
    display: flex; align-items: center; justify-content: center;
    padding: 32px 16px;
}
@media (prefers-color-scheme: dark) {
    .install-wrap { background: linear-gradient(135deg, #0d2543 0%, #0f1419 60%); }
}
.install-card {
    width: 100%; max-width: 640px;
    background: var(--card-bg);
    border-radius: 14px;
    box-shadow: 0 12px 48px rgba(0,0,0,0.12);
    overflow: hidden;
    border: 1px solid var(--border);
    animation: fadeUp 0.45s cubic-bezier(0.4,0,0.2,1);
}
@keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

/* 顶部进度条 */
.install-progress { height: 3px; background: var(--divider); position: relative; overflow: hidden; }
.install-progress .bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--primary-light));
    transition: width 0.45s cubic-bezier(0.4,0,0.2,1);
}

/* 头部 */
.install-head {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff; padding: 24px 32px;
    display: flex; align-items: center; gap: 16px;
}
.install-head .logo-ic {
    width: 48px; height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; backdrop-filter: blur(4px);
}
.install-head .htxt h1 { font-size: 20px; font-weight: 700; letter-spacing: 0.5px; }
.install-head .htxt .sub { font-size: 12.5px; opacity: 0.9; margin-top: 2px; }
.install-head .ver {
    margin-left: auto;
    background: rgba(255,255,255,0.2);
    padding: 4px 12px; border-radius: 999px;
    font-size: 12px; font-weight: 600; flex-shrink: 0;
}

/* 主体 */
.install-body { padding: 26px 32px 30px; }

.step-title {
    display: flex; align-items: center; gap: 10px;
    font-size: 16px; font-weight: 600; color: var(--text);
    margin-bottom: 16px;
}
.step-title svg { width: 20px; height: 20px; color: var(--primary); flex-shrink: 0; }

/* 步骤指示 */
.install-steps {
    display: flex; align-items: center;
    margin-bottom: 24px;
    padding: 14px 10px;
    background: var(--bg-soft);
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
}
.install-steps .istep {
    display: flex; align-items: center; gap: 7px;
    color: var(--text-muted); font-size: 12px;
    flex: 1; min-width: 0;
}
.install-steps .istep .num {
    width: 26px; height: 26px; border-radius: 50%;
    background: var(--bg-elevated); border: 1px solid var(--border-2);
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 12px; flex-shrink: 0;
    transition: all var(--transition);
}
.install-steps .istep .istep-label {
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.install-steps .istep.active .num {
    background: var(--primary); color: #fff; border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-bg);
}
.install-steps .istep.active { color: var(--primary); font-weight: 600; }
.install-steps .istep.done .num { background: var(--success); color: #fff; border-color: var(--success); }
.install-steps .istep.done { color: var(--success); }
.install-steps .line {
    flex: 0 0 14px; height: 2px; background: var(--border);
    margin: 0 4px; transition: background var(--transition);
}
.install-steps .line.done { background: var(--success); }
@media (max-width: 560px) {
    .install-steps .istep .istep-label { display: none; }
    .install-steps .istep { flex: 0 0 auto; }
    .install-steps .line { flex: 1 1 auto; }
}

/* 警告框 */
.alert {
    border-radius: var(--radius-md); padding: 12px 16px;
    font-size: 13px; line-height: 1.6; margin-bottom: 18px;
    display: flex; gap: 10px; align-items: flex-start;
}
.alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
.alert-error { background: rgba(255,77,79,0.08); border: 1px solid rgba(255,77,79,0.3); color: var(--danger); }
.alert-success { background: rgba(0,185,107,0.08); border: 1px solid rgba(0,185,107,0.3); color: var(--success); }
.alert-info { background: var(--primary-bg); border: 1px solid var(--primary-bg-2); color: var(--primary); }
.alert-warning { background: rgba(250,173,20,0.1); border: 1px solid rgba(250,173,20,0.3); color: var(--warning); }

/* 协议 */
.license-box {
    background: var(--bg-soft); border: 1px solid var(--border);
    border-radius: var(--radius-md); padding: 16px 18px;
    max-height: 240px; overflow-y: auto;
    font-size: 13px; line-height: 1.9; color: var(--text-2);
}
.license-box p { margin-bottom: 10px; }
.license-box p:last-child { margin-bottom: 0; }
.license-box b { color: var(--text); }
.agree-row {
    margin: 16px 0 0; display: flex; align-items: center; gap: 8px;
    font-size: 13px;
}
.agree-row input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary); }
.agree-row label { cursor: pointer; user-select: none; }

/* 环境 */
.env-list {
    background: var(--bg-soft); border: 1px solid var(--border);
    border-radius: var(--radius-md); padding: 4px 16px;
}
.env-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 11px 0; border-bottom: 1px solid var(--divider);
    gap: 12px;
}
.env-item:last-child { border-bottom: none; }
.env-item .name { display: flex; align-items: center; gap: 8px; font-size: 13.5px; min-width: 0; }
.env-item .name .info { color: var(--text-muted); font-size: 12px; }
.env-item .res { font-size: 13px; display: flex; align-items: center; gap: 5px; flex-shrink: 0; }
.env-ok { color: var(--success); display: inline-flex; align-items: center; gap: 4px; font-weight: 500; }
.env-no { color: var(--danger); display: inline-flex; align-items: center; gap: 4px; font-weight: 500; }
.env-ok svg, .env-no svg { width: 16px; height: 16px; }

/* 表单网格 */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 560px) { .form-grid { grid-template-columns: 1fr; } }

/* 步骤底部按钮 */
.step-foot {
    margin-top: 22px; display: flex; justify-content: space-between; align-items: center; gap: 10px;
}
.step-foot .right { display: flex; gap: 10px; align-items: center; }

/* 测试连接结果 */
.test-result { font-size: 12.5px; margin-top: 4px; min-height: 18px; }

/* 完成页 */
.done-screen { text-align: center; padding: 18px 0 8px; }
.done-icon {
    width: 76px; height: 76px; margin: 0 auto 18px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--success), #5cdb8f);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(0,185,107,0.3);
    animation: pop 0.5s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes pop { 0% { transform: scale(0); } 100% { transform: scale(1); } }
.done-screen h3 { font-size: 22px; margin-bottom: 8px; }
.done-screen .ds-sub { color: var(--text-muted); margin-bottom: 6px; font-size: 14px; }
.done-screen .ds-tip { color: var(--text-muted); font-size: 12px; margin-bottom: 26px; }
.done-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

/* 移动端 */
@media (max-width: 560px) {
    .install-head { padding: 18px 20px; gap: 12px; }
    .install-head .logo-ic { width: 40px; height: 40px; }
    .install-head .htxt h1 { font-size: 17px; }
    .install-head .ver { display: none; }
    .install-body { padding: 22px 18px 24px; }
    .step-foot { flex-wrap: wrap; }
}
</style>
</head>
<body>
<div class="install-wrap">
  <div class="install-card">
    <!-- 顶部进度条 -->
    <div class="install-progress"><div class="bar" style="width:<?php echo ($step / 5) * 100; ?>%"></div></div>

    <!-- 头部 -->
    <div class="install-head">
      <div class="logo-ic">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
      <div class="htxt">
        <h1>管备云备案系统</h1>
        <div class="sub">专业 ICP 备案服务管理平台 · 安装向导</div>
      </div>
      <span class="ver"><?php echo GB_VERSION; ?></span>
    </div>

    <div class="install-body">
      <!-- 步骤指示 -->
      <div class="install-steps">
        <?php for ($i = 1; $i <= 5; $i++):
          $cls = $i < $step ? 'done' : ($i === $step ? 'active' : '');
          $lineCls = $i < $step ? 'done' : '';
        ?>
          <div class="istep <?php echo $cls; ?>">
            <div class="num">
              <?php if ($i < $step): ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              <?php else: echo $i; endif; ?>
            </div>
            <span class="istep-label"><?php echo $stepNames[$i - 1]; ?></span>
          </div>
          <?php if ($i < 5): ?>
            <div class="line <?php echo $lineCls; ?>"></div>
          <?php endif; ?>
        <?php endfor; ?>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-error">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <div><?php foreach ($errors as $e): ?><div>• <?php echo h($e); ?></div><?php endforeach; ?></div>
        </div>
      <?php endif; ?>

      <!-- Step 1: 协议 -->
      <?php if ($step == 1): ?>
        <div class="step-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/></svg>
          安装许可协议
        </div>
        <div class="license-box">
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
        <div class="agree-row">
          <input type="checkbox" id="agree">
          <label for="agree">我已阅读并同意以上安装许可协议</label>
        </div>
        <div class="step-foot">
          <span></span>
          <div class="right">
            <button class="btn btn-primary" id="next1" disabled onclick="location.href='index.php?step=2'">下一步</button>
          </div>
        </div>
        <script>
        (function(){
          var cb = document.getElementById('agree');
          var btn = document.getElementById('next1');
          cb.addEventListener('change', function(){ btn.disabled = !cb.checked; });
        })();
        </script>

      <!-- Step 2: 条件检测 -->
      <?php elseif ($step == 2):
        $env = check_env();
        $allOk = true;
        foreach ($env as $it) if (!$it['ok']) $allOk = false;
      ?>
        <div class="step-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          环境条件检测
        </div>
        <div class="env-list">
          <?php foreach ($env as $it): ?>
            <div class="env-item">
              <div class="name">
                <?php echo h($it['name']); ?>
                <?php if ($it['info']): ?><span class="info">(<?php echo h($it['info']); ?>)</span><?php endif; ?>
              </div>
              <div class="res">
                <?php if ($it['ok']): ?>
                  <span class="env-ok">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    通过
                  </span>
                <?php else: ?>
                  <span class="env-no">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    未通过
                  </span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if ($allOk): ?>
          <div class="alert alert-success" style="margin-top:16px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <div>环境检测全部通过，可以继续安装。</div>
          </div>
        <?php else: ?>
          <div class="alert alert-warning" style="margin-top:16px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>部分环境条件未通过，建议修复后继续。也可点击"仍要继续"强制安装。</div>
          </div>
        <?php endif; ?>
        <div class="step-foot">
          <button class="btn" onclick="location.href='index.php?step=1'">上一步</button>
          <div class="right">
            <button class="btn btn-primary" onclick="location.href='index.php?step=3'"><?php echo $allOk ? '下一步' : '仍要继续'; ?></button>
          </div>
        </div>

      <!-- Step 3: 数据库配置 -->
      <?php elseif ($step == 3): ?>
        <div class="step-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
          数据库配置
        </div>
        <div class="alert alert-info">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <div>请确保数据库已创建并具有访问权限，提交后将自动导入数据表。</div>
        </div>
        <form method="post" id="dbForm">
          <input type="hidden" name="action" value="db">
          <div class="form-grid">
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
          <div class="form-grid">
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
            <div class="form-hint">默认前缀 gb_，如需修改请保留下划线结尾</div>
          </div>
          <div class="test-result" id="testResult"></div>
          <div class="step-foot">
            <button type="button" class="btn" onclick="location.href='index.php?step=2'">上一步</button>
            <div class="right">
              <button type="button" class="btn" id="testBtn" onclick="testConn()">测试连接</button>
              <button type="submit" class="btn btn-primary">测试并导入数据库</button>
            </div>
          </div>
        </form>
        <script>
        function testConn(){
          var f = document.getElementById('dbForm');
          var data = new FormData(f);
          data.set('action', 'test_db');
          var btn = document.getElementById('testBtn');
          var res = document.getElementById('testResult');
          btn.disabled = true; btn.textContent = '测试中...';
          res.innerHTML = '<span style="color:var(--text-muted)">正在测试连接...</span>';
          fetch('index.php', { method: 'POST', body: data })
            .then(function(r){ return r.json(); })
            .then(function(j){
              var ico = j.ok
                ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:-2px;"><path d="M20 6L9 17l-5-5"/></svg>'
                : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:-2px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
              res.innerHTML = '<span class="' + (j.ok ? 'env-ok' : 'env-no') + '">' + ico + ' ' + j.msg + '</span>';
            })
            .catch(function(){ res.innerHTML = '<span class="env-no">请求失败，请重试</span>'; })
            .finally(function(){ btn.disabled = false; btn.textContent = '测试连接'; });
        }
        </script>

      <!-- Step 4: 管理员配置 -->
      <?php elseif ($step == 4): ?>
        <div class="step-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          管理员账号配置
        </div>
        <div class="alert alert-success">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <div>数据库连接并导入成功，请配置超级管理员账号。</div>
        </div>
        <form method="post">
          <input type="hidden" name="action" value="admin">
          <div class="form-group">
            <label class="form-label">管理员用户名 <span class="req">*</span></label>
            <input class="form-control" name="admin_user" value="admin" required minlength="3">
            <div class="form-hint">至少 3 位字符</div>
          </div>
          <div class="form-group">
            <label class="form-label">管理员密码 <span class="req">*</span></label>
            <input class="form-control" type="password" name="admin_pass" required minlength="6">
            <div class="form-hint">至少 6 位字符，建议包含字母与数字</div>
          </div>
          <div class="form-group">
            <label class="form-label">管理员邮箱</label>
            <input class="form-control" type="email" name="admin_email" value="">
          </div>
          <div class="step-foot">
            <button type="button" class="btn" onclick="location.href='index.php?step=3'">上一步</button>
            <div class="right">
              <button type="submit" class="btn btn-primary">完成安装</button>
            </div>
          </div>
        </form>

      <!-- Step 5: 完成 -->
      <?php elseif ($step == 5): ?>
        <div class="done-screen">
          <div class="done-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
          </div>
          <h3>安装完成！</h3>
          <p class="ds-sub">管备云备案系统已成功安装，欢迎使用。</p>
          <p class="ds-tip">为安全起见，请删除或重命名 install 目录。</p>
          <div class="done-actions">
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
