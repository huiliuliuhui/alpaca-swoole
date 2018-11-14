<?php
/**
 * 该配置文件需要根据服务器目录来判断环境
 */

$envs = [
    'dev' => [
       'master' => [
           'host' => 'xxx',
           'port' => "xxx",
           'select'=>0
       ],
    ],

    'Test' => [
        'master' => [
            'host' => 'xxx',
            'port' => "xxx",
            'select'=>0
        ],
    ],

    'vagrant_data' => [
        'master' =>[
            //我机器的本地环境
            'host' => '127.0.0.1',
            'port' => 6379,
            'select'=>0
            ]
    ],

    'default' => [
        'master' => [
            'host' => 'xxx',
            'port' => "xxx",
            'select'=>0
        ],
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


