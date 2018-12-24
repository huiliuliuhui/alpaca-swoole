<?php
/**
 * swoole服务配置文件
 *
 */
$config = [

    /**
     * 需要修改
     */
    'server' => [
        'host'   => '0.0.0.0',//监听的HOST
        'port'   => '4092',//监听的端口
    ],


    /**
     * 根据服务器情况需要修改
     */
    'swoole' => [
        'log_file'        => ROOT_PATH . '/log/swoole.log',
        'worker_num'      => 2,//worker线程数量，一般设为cpu核数的1-2倍
        'max_request'     => 0, //0-表示不会重启worker，不要修改这里，因为webim服务是有状态的服务
        'task_worker_num' => 0,//task线程数量
        'daemonize'       => 0,//是否要作为守护进程
    ],


    /**
     * 该配置不需要修改
     */
    'webim' => [
        'log_file' => ROOT_PATH . '/log/webim.log',//聊天记录存储的目录
        'send_interval_limit' => 1, //只允许1秒发送一次
    ],



];



return $config;