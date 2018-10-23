<?php
/**
 * 对容器的封装，暴露三个接口给应用程序调用
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/22 0022
 * Time: 上午 0:01
 */

namespace Kernel;
use Kernel\Container\Container;

class App extends Container
{
    static function bind($abstract, $concrete = null, $shared = false){
        parent::bind($abstract, $concrete, $shared);
    }

    static function make($abstract, $parameters = []){
        return parent::make($abstract, $parameters);
    }

    static function singleton($abstract, $concrete, $shared = true){
        parent::singleton($abstract, $concrete, $shared);
    }

}