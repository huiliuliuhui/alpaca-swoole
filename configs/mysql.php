<?php

/**
 *
 * mysql配置
 * 该配置文件需要根据服务器目录来判断环境
 */

$envs = [
    'dev' => [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "kz_decision",
        'charset'    => "utf8",
    ],

    'test' => [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "kz_decision",
        'charset'    => "utf8",
    ],

    'local' => [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "demo",
        'charset'    => "utf8",
    ],


];

$env = getenv('webim_env');

$config = $envs[$env];

return $config;