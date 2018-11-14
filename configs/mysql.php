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

    'Test' => [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "kz_decision",
        'charset'    => "utf8",
    ],

    'vagrant_data' => [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "kz_decision",
        'charset'    => "utf8",
    ],

    'default' => [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "kz_decision",
        'charset'    => "utf8",
    ]

];

$project_path = \Swoole::getInstance()->config->config_path[0];

foreach ($envs as $env => $cg){
    if (strpos($project_path,$env) !== false || $env == "default"){
        $config = $cg;
        break;
    }
}


return $config;