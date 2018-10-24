<?php
/**
 * 自动加载服务提供者
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/22 0022
 * Time: 下午 18:34
 */
namespace Kernel\Bootstrap;

$app = require_once __DIR__ . "/../../configs/app.php";


$providers = $app['providers'];
/**
 * 先统一注册，再执行引导方法
 * 这是为了将依赖完全加载到容器中，防止执行引导方法时无法找到依赖类而报错
 */

if (!empty($providers)){

    foreach ($providers as $provider){

        $instance = new $provider;

        $instance->register();

    }


    foreach ($providers as $provider){

        $instance = new $provider;

        $instance->boot();

    }
}

