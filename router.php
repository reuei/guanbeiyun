<?php
/**
 * PHP 内置服务器路由器 (开发环境)
 * 用法: php -S 127.0.0.1:8080 -t public router.php
 *
 * 文档根 = public/:
 *  - /install/*  -> public/install/index.php
 *  - 静态文件    -> 直接返回
 *  - 其他请求    -> public/index.php
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicRoot = __DIR__ . '/public';

// install 子目录
if (strpos($uri, '/install') === 0) {
    $installFile = $publicRoot . '/install/index.php';
    if (is_file($installFile)) {
        chdir(dirname($installFile));
        require $installFile;
        return true;
    }
}

// 静态文件: 按请求路径在 public/ 下查找
if ($uri !== '/' && preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf|map)$/i', $uri)) {
    $candidate = $publicRoot . $uri;
    if (is_file($candidate)) {
        return false; // 让 PHP 内置服务器直接返回该文件
    }
}

// 默认走应用入口
chdir($publicRoot);
require $publicRoot . '/index.php';
