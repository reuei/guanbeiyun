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

/** 获取站点设置 (从数据库, 使用全局数组以保证 set_site_config 同请求内可见) */
function site_config($key = null, $default = null)
{
    if (!isset($GLOBALS['_gb_site_config_cache'])) {
        $GLOBALS['_gb_site_config_cache'] = [];
        try {
            $rows = db()->query("SELECT * FROM " . db()->table('config'));
            foreach ($rows as $row) {
                $GLOBALS['_gb_site_config_cache'][$row['name']] = $row['value'];
            }
        } catch (Throwable $e) {
            // 安装前表不存在
        }
    }
    if ($key === null) return $GLOBALS['_gb_site_config_cache'];
    return $GLOBALS['_gb_site_config_cache'][$key] ?? $default;
}

/** 设置配置项 (写入数据库 + 同步更新缓存) — 使用 INSERT ON DUPLICATE KEY UPDATE 原子操作 */
function set_site_config($name, $value)
{
    if (!isset($GLOBALS['_gb_site_config_cache'])) {
        site_config();
    }
    $now = date('Y-m-d H:i:s');
    $p = db()->prefix();
    $sql = "INSERT INTO {$p}config (name, value, updated_at) VALUES (?, ?, ?) "
         . "ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at)";
    db()->execute($sql, [$name, $value, $now]);
    $GLOBALS['_gb_site_config_cache'][$name] = $value;
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

/** 网站是否处于维护模式 (排除管理员与后台/登录接口) */
function is_maintenance_mode()
{
    $path = request_path();
    // 后台、登录、API 与安装程序不拦截
    $allow = ['/admin', '/login', '/logout', '/api/', '/captcha', '/oauth'];
    foreach ($allow as $a) {
        if (strpos($path, $a) === 0) return false;
    }
    if (is_admin_logged_in()) return false;
    return site_config('maintenance_enabled') == '1';
}

/** 获取用户的认证标识列表 (通过其已通过的认证申请) */
function user_certifications($userId)
{
    $certs = [];
    try {
        $rows = db()->query(
            "SELECT a.type, a.name, c.image, c.info, c.icon_style FROM " . db()->table('applications') . " a " .
            "LEFT JOIN " . db()->table('certifications') . " c ON c.name = a.name AND c.status=1 " .
            "WHERE a.user_id = ? AND a.status = 1 AND a.type IN ('enterprise','personal','partner') ORDER BY a.id DESC",
            [$userId]
        );
        foreach ($rows as $r) {
            // 若未配置对应认证图片, 用类型回退
            if (empty($r['image'])) {
                $r['image'] = '';
                $r['icon_style'] = $r['icon_style'] ?: ('cert-' . $r['type']);
            }
            $certs[] = $r;
        }
    } catch (Throwable $e) {}
    return $certs;
}

/** 获取聊天室违禁词列表 */
function chat_forbidden_words()
{
    static $words = null;
    if ($words === null) {
        $words = [];
        try {
            $rows = db()->query("SELECT word FROM " . db()->table('chat_words'));
            foreach ($rows as $r) $words[] = $r['word'];
        } catch (Throwable $e) {}
    }
    return $words;
}

/** 检查用户是否被禁言, 返回 [bool, 截止时间] */
function chat_user_banned($userId)
{
    try {
        $row = db()->queryOne(
            "SELECT * FROM " . db()->table('chat_banned') . " WHERE user_id = ? AND banned_until > NOW() ORDER BY banned_until DESC LIMIT 1",
            [$userId]
        );
        if ($row) return [true, $row['banned_until'], $row['reason']];
    } catch (Throwable $e) {}
    return [false, null, null];
}

/** 生成不重复的备案号 格式: 管ICP备xxxxxxxx号 */
function gen_icp_no()
{
    $p = db()->prefix();
    for ($i = 0; $i < 10; $i++) {
        $seq = str_pad((string)mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        $no = '管ICP备' . $seq . '号';
        $exists = db()->queryOne("SELECT id FROM {$p}filings WHERE icp_no = ?", [$no]);
        if (!$exists) return $no;
    }
    // 极端情况回退
    return '管ICP备' . date('Ymd') . mt_rand(1000, 9999) . '号';
}

/** 获取随机一言 */
function hitokoto()
{
    $list = [
        '愿你历尽千帆，归来仍是少年。',
        '生活明朗，万物可爱。',
        '凡是过往，皆为序章。',
        '心若向阳，无谓悲伤。',
        '不忘初心，方得始终。',
        '星光不问赶路人，时光不负有心人。',
        '愿你成为自己的太阳，无需凭借谁的光。',
        '所有的美好，都值得等待。',
    ];
    return $list[array_rand($list)];
}

/** 获取用户未读通知数量 */
function unread_notification_count($userId)
{
    try {
        $p = db()->prefix();
        // 直接发给本人的未读
        $c1 = (int)db()->queryScalar("SELECT COUNT(*) FROM {$p}notifications WHERE user_id = ? AND is_read = 0", [$userId]);
        // 全体通知中未读的 (排除已在 reads 表中标记的)
        $c2 = (int)db()->queryScalar(
            "SELECT COUNT(*) FROM {$p}notifications n WHERE n.user_id = 0 AND NOT EXISTS (SELECT 1 FROM {$p}notification_reads r WHERE r.notification_id = n.id AND r.user_id = ?)",
            [$userId]
        );
        return $c1 + $c2;
    } catch (Throwable $e) {
        return 0;
    }
}

/** 发送通知给用户 (v4: 统一通知入口) */
function send_notification($userId, $title, $content, $type = 'system', $link = '')
{
    try {
        db()->insert('notifications', [
            'user_id'    => (int)$userId,
            'title'      => $title,
            'content'    => $content,
            'type'       => $type,
            'link'       => $link,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $e) {}
}

/** 发送通知给管理员 */
function send_admin_notification($title, $content, $type = 'system')
{
    try {
        db()->insert('admin_notifications', [
            'title'      => $title,
            'content'    => $content,
            'type'       => $type,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $e) {}
}

/** 获取管理员未读通知数量 */
function unread_admin_notification_count()
{
    try {
        return (int)db()->queryScalar("SELECT COUNT(*) FROM " . db()->table('admin_notifications') . " WHERE is_read = 0");
    } catch (Throwable $e) {
        return 0;
    }
}

/** 检查当前用户是否被某用户拉黑 */
function is_blocked_by($blockedUserId, $byUserId)
{
    try {
        $row = db()->queryOne(
            "SELECT id FROM " . db()->table('user_blocks') . " WHERE user_id = ? AND blocked_id = ?",
            [$byUserId, $blockedUserId]
        );
        return $row ? true : false;
    } catch (Throwable $e) {
        return false;
    }
}

/** 获取备案号前缀图片 (后台 ICP 图片管理) */
function icp_prefix_images()
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    try {
        $cache = db()->query("SELECT * FROM " . db()->table('icp_images') . " WHERE status=1 ORDER BY sort DESC, id ASC");
    } catch (Throwable $e) {}
    return $cache;
}

/** 获取用户已通过的备案号列表 (用于底部萌ICP链接) */
function user_filing_links($userId)
{
    $links = [];
    try {
        $rows = db()->query(
            "SELECT icp_no, site_name, site_url FROM " . db()->table('filings')
            . " WHERE user_id = ? AND status = 1 AND icp_no IS NOT NULL AND icp_no != ''",
            [$userId]
        );
        foreach ($rows as $r) {
            $links[] = $r;
        }
    } catch (Throwable $e) {}
    return $links;
}

/** 获取随机背景图 URL (个人中心用, 每次刷新不同) */
function random_bg_image()
{
    $imgs = [
        'https://picsum.photos/seed/' . mt_rand(1, 9999) . '/1200/400',
    ];
    return $imgs[0];
}
