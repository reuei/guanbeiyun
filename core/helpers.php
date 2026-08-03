<?php
/**
 * 全局辅助函数
 */

/** 读取配置 (支持点号取值) */
function config($key = null, $default = null)
{
    static $config = null;
    if ($config === null) {
        $configFile = __DIR__ . '/../config/config.php';
        if (is_file($configFile)) {
            $config = require $configFile;
        } else {
            $config = require __DIR__ . '/../config/config.example.php';
        }
    }
    if ($key === null) {
        return $config;
    }
    $keys = explode('.', $key);
    $val = $config;
    foreach ($keys as $k) {
        if (!is_array($val) || !array_key_exists($k, $val)) {
            return $default;
        }
        $val = $val[$k];
    }
    return $val;
}

/** 数据库实例 */
function db()
{
    return Database::instance();
}

/** URL 跳转 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/** JSON 输出 */
function json($data, $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** JSON 成功 */
function ok($data = null, $msg = '操作成功')
{
    json(['code' => 0, 'msg' => $msg, 'data' => $data]);
}

/** JSON 失败 */
function fail($msg = '操作失败', $code = 1, $data = null)
{
    json(['code' => $code, 'msg' => $msg, 'data' => $data]);
}

/** 获取站点设置 (从数据库) */
function site_config($key = null, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = db()->query("SELECT * FROM " . db()->table('config'));
            foreach ($rows as $row) {
                $cache[$row['name']] = $row['value'];
            }
        } catch (Throwable $e) {
            // 安装前表不存在
        }
    }
    if ($key === null) return $cache;
    return $cache[$key] ?? $default;
}

/** 站点根URL */
function site_url($path = '')
{
    $base = site_config('site_url', '');
    if (!$base) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        // 计算子目录前缀 (文档根 = public/)
        $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        // 兼容: 如果在 install 子目录, 去掉 /install
        if (substr($dir, -8) === '/install') $dir = substr($dir, 0, -8);
        $base = rtrim($base . $dir, '/');
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/** 静态资源URL */
function asset($path)
{
    return site_url(ltrim($path, '/'));
}

/** 转义输出 */
function e($str)
{
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/** 当前登录管理员 */
function admin_user()
{
    return $_SESSION['gb_admin'] ?? null;
}

/** 当前登录用户 */
function current_user()
{
    return $_SESSION['gb_user'] ?? null;
}

/** 是否登录 */
function is_logged_in()
{
    return !empty($_SESSION['gb_user']);
}

/** 是否管理员登录 */
function is_admin_logged_in()
{
    return !empty($_SESSION['gb_admin']);
}

/** 获取首页备案公示 (后台可配置, type=filing) */
function filing_publicity($limit = 10)
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    try {
        $limit = (int)$limit;
        $cache = db()->query(
            "SELECT title, content, link, created_at FROM " . db()->table('publicity') .
            " WHERE type='filing' AND status=1 ORDER BY sort DESC, id DESC LIMIT $limit"
        );
    } catch (Throwable $e) {}
    return $cache;
}

/** 密码加密 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/** 验证密码 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/** 生成随机字符串 */
function random_str($length = 16)
{
    return substr(bin2hex(random_bytes(ceil($length / 2))), 0, $length);
}

/** 生成 CSRF token */
function csrf_token()
{
    if (empty($_SESSION['gb_csrf'])) {
        $_SESSION['gb_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['gb_csrf'];
}

/** 验证 CSRF */
function csrf_verify()
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? '');
    if (!hash_equals($_SESSION['gb_csrf'] ?? '', $token)) {
        fail('CSRF 验证失败', 419);
    }
}

/** 获取请求输入 */
function input($key = null, $default = null)
{
    $input = array_merge($_GET, $_POST);
    $raw = json_decode(file_get_contents('php://input'), true);
    if (is_array($raw)) {
        $input = array_merge($input, $raw);
    }
    if ($key === null) return $input;
    return $input[$key] ?? $default;
}

/** 计算当前请求的相对路径 (去除子目录前缀, 用于路由匹配/鉴权判断) */
function request_path()
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if ($scriptDir !== '' && $scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
        $uri = substr($uri, strlen($scriptDir));
    }
    return '/' . trim($uri, '/');
}

/** 分页参数 */
function page_params()
{
    $page = max(1, (int)input('page', 1));
    $size = min(100, max(1, (int)input('size', 15)));
    return [$page, $size, ($page - 1) * $size];
}

/** 记录日志 */
function log_action($type, $content, $uid = 0, $role = 'user')
{
    try {
        db()->insert('logs', [
            'type'       => $type,
            'content'    => $content,
            'user_id'    => $uid ?: (current_user()['id'] ?? 0),
            'role'       => $role,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $e) {}
}

/** 记录操作日志 */
function log_op($content)
{
    log_action('operation', $content, current_user()['id'] ?? 0, 'user');
}

/** 记录登录日志 */
function log_login($content, $uid = 0, $role = 'user')
{
    log_action('login', $content, $uid, $role);
}

/** 当前语言 */
function lang()
{
    return $_SESSION['gb_lang'] ?? 'zh';
}

/** 翻译 */
function t($key)
{
    static $lang = null;
    if ($lang === null) {
        $l = lang();
        $file = __DIR__ . "/../lang/$l.php";
        $lang = is_file($file) ? require $file : [];
    }
    return $lang[$key] ?? $key;
}

/** 文件大小格式化 */
function size_format($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/** 时间格式化 */
function time_ago($datetime)
{
    $now = time();
    $ts = strtotime($datetime);
    $diff = $now - $ts;
    if ($diff < 60) return '刚刚';
    if ($diff < 3600) return floor($diff / 60) . '分钟前';
    if ($diff < 86400) return floor($diff / 3600) . '小时前';
    if ($diff < 2592000) return floor($diff / 86400) . '天前';
    return date('Y-m-d', $ts);
}
