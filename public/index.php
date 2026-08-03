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

// 路由
$router = new Router();
$routesFile = GB_ROOT . '/app/routes.php';
if (is_file($routesFile)) {
    require $routesFile;
}

// 解析路径 (去除子目录前缀)
$router->dispatch(request_path());
