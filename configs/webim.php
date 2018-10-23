<?php

$config['swoole'] = [
    'log_file'        => ROOT_PATH . '/log/swoole.log',
    'worker_num'      => 4,//worker线程数量，cpu的1-2倍
    'max_request'     => 0, //0-表示不会重启worker，不要修改这里
    'task_worker_num' => 16,//task线程数量
    'daemonize'       => 0,//是否要作为守护进程
];

$config['webim'] = [
    'log_file' => ROOT_PATH . '/log/webim.log',//聊天记录存储的目录
    'send_interval_limit' => 1, //只允许1秒发送一次
];

$config['server'] = [
    'host'   => '0.0.0.0',//监听的HOST
    'port'   => '4092',//监听的端口
];

//mysql配置
$host = \Swoole::getInstance()->config->config_path[0];

if(strpos($host,"dev")!==false){
    //dev环境
    $config['mysql'] = [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "kz_decision",
        'charset'    => "utf8",
    ];


}elseif(strpos($host,"Test")!==false){
    //test环境
    $config['mysql'] = [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "kz_decision",
        'charset'    => "utf8",
    ];

}elseif(strpos($host,"vagrant_data") !== false){
    $config['mysql'] = [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "kz_decision",
        'charset'    => "utf8",
    ];


} else{
    //生成环境
    $config['mysql'] = [
        'driver'       => "pdo",
        'type'       => "mysql",
        'hostname'       => "127.0.0.1",
        'hostport'       => 3306,
        'username'       => "root",
        'password'     => "123456",
        'database'       => "kz_decision",
        'charset'    => "utf8",
    ];
}

return $config;