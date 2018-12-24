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

    'test' => [
        'master' => [
            'host' => 'xxx',
            'port' => "xxx",
            'select'=>0
        ],
    ],

    'local' => [
        'master' =>[
            //我机器的本地环境
            'host' => 'xx',
            'port' => 00,
            'select'=>0
            ]
    ],

];

$env = getenv('webim_env');

$config = $envs[$env];

return $config;


