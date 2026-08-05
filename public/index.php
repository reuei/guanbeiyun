<?php
/**
 * 管备云备案系统 - 入口文件
 */
define('GB_START', microtime(true));
define('GB_ROOT', dirname(__DIR__));

// 自动加载核心类
spl_autoload_register(function ($class) {
    $core = GB_ROOT . '/core/' . $class . '.php';
    if (is_file($core)) require $core;
    $ctrl = GB_ROOT . '/app/controllers/' . $class . '.php';
    if (is_file($ctrl)) require $ctrl;
});

require GB_ROOT . '/core/helpers.php';

// 启动会话
$life = config('session.lifetime', 7200);
session_set_cookie_params($life, '/', '', true, true);
session_start();

// 错误处理
if (config('app.debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', 0);
    set_exception_handler(function ($e) {
        error_log($e->getMessage());
        if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'json') !== false) {
            fail('服务器错误', 500);
        }
        http_response_code(500);
        require GB_ROOT . '/app/views/errors/500.php';
    });
}

// 时区
date_default_timezone_set('Asia/Shanghai');

// 检测是否已安装
if (!is_file(GB_ROOT . '/config/config.php') && strpos($_SERVER['REQUEST_URI'] ?? '', '/install') === false) {
    // 跳转到安装程序 (install 位于 public/install/)
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $installUrl = $base . '/install/';
    header("Location: $installUrl");
    exit;
}

// 网站维护模式 (排除管理员与后台/登录等)
if (is_file(GB_ROOT . '/config/config.php') && is_maintenance_mode()) {
    http_response_code(503);
    header('Retry-After: 3600');
    $site = site_config();
    $title = $site['maintenance_title'] ?? '网站维护中';
    $content = $site['maintenance_content'] ?? '系统正在维护升级中，请稍后访问。';
    $recover = $site['maintenance_recover_time'] ?? '';
    $siteName = $site['site_name'] ?? '管备云备案系统';
    $siteLogo = $site['site_logo'] ?? '';
    echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>' . e($title) . ' - ' . e($siteName) . '</title><link rel="stylesheet" href="' . asset('assets/css/theme.css') . '"><link rel="stylesheet" href="' . asset('assets/css/site.css') . '"><style>.maint-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}.maint-card{max-width:520px;text-align:center;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:48px 36px;box-shadow:0 8px 32px rgba(0,0,0,.06);}.maint-ic{width:80px;height:80px;margin:0 auto 22px;border-radius:50%;background:var(--warning);display:flex;align-items:center;justify-content:center;color:#fff;}.maint-card h1{font-size:24px;margin:0 0 12px;}.maint-card p{color:var(--text-2);line-height:1.9;margin:0 0 8px;}.maint-recover{margin-top:18px;padding:10px 16px;background:var(--bg-soft);border-radius:6px;color:var(--primary);font-size:14px;display:inline-block;}</style></head><body><div class="maint-wrap"><div class="maint-card"><div class="maint-ic"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><h1>' . e($title) . '</h1><p>' . $content . '</p>' . ($recover ? '<div class="maint-recover">预计恢复时间：' . e($recover) . '</div>' : '') . '</div></div></body></html>';
    exit;
}

// 路由
$router = new Router();
$routesFile = GB_ROOT . '/app/routes.php';
if (is_file($routesFile)) {
    require $routesFile;
}

// 解析路径 (去除子目录前缀)
$router->dispatch(request_path());
