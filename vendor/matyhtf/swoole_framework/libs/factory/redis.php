<?php
global $php;

$config = $php->config['redis'][$php->factory_key];
if (empty($config) or empty($config['host']))
{
    throw new Exception("require redis[$php->factory_key] config.");
}

//if (empty($config['port']))
//{
//    $config['port'] = (strpos($_SERVER['HTTP_HOST'],"local.")===false)?"20002":"6379";
//    //$config['port'] = 20003;
//}

if (empty($config["pconnect"]))
{
    $config["pconnect"] = false;
}

if (empty($config['timeout']))
{
    $config['timeout'] = 0;
}
echo "redis:";
print_r($config);
$redis = new Swoole\Component\Redis($config);
return $redis;