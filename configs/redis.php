<?php
$host = \Swoole::getInstance()->config->config_path[0];
if(strpos($host,"dev")!==false){
    //dev环境
    $redis['master'] = array(
        'host' => 'xxx',
        'port' => "xxx",
        'select'=>0
    );
}elseif(strpos($host,"test")!==false){
    //test环境
    $redis['master'] = array(
        'host' => 'xxx',
        'port' => "xxx",
        'select'=>0
    );
}elseif(strpos($host,"vagrant_data") !== false){
    //本地环境
    $redis['master'] = array(
        'host' => '127.0.0.1',
        'port' => 6379,
        'select'=>0
    );


}else{
    //生成环境
    $redis['master'] = array(
        'host' => 'prd',
        'port' => "xxx",
        'select'=>0
    );
}


return $redis;