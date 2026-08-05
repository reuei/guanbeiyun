<?php
/**
 * 管备云备案系统 - 配置文件示例
 * 安装完成后会生成 config.php
 */

return [
    // 数据库配置
    'database' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'guanbeiyun',
        'user'     => 'root',
        'pass'     => '',
        'charset'  => 'utf8mb4',
        'prefix'   => 'gb_',
    ],

    // 站点配置
    'site' => [
        'name'        => '管备云备案系统',
        'url'         => '',
        'install_time'=> '',
        'version'     => '1.0.2',
    ],

    // 会话
    'session' => [
        'prefix' => 'gb_',
        'lifetime' => 7200,
    ],

    // 上传
    'upload' => [
        'path' => __DIR__ . '/../public/uploads',
        'max_size' => 5242880, // 5MB
        'allow' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
    ],
];
