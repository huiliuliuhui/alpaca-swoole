<?php

/**
 * 定义服务提供者的规范
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/22 0022
 * Time: 下午 18:32
 */
namespace Kernel\Provider;
use Kernel\App;

class ServiceProvider implements ServiceProviderInterface
{


    public $redis = null;

    function __construct()
    {
        $this->redis = App::make("redis");
    }

    /**
     * 注册到容器
     * @return mixed
     */
    public function register(){

    }


    /**
     * 引导方法
     * @return mixed
     */
    public function boot(){

    }


    /**
     * 提供给服务提供者的容器绑定封装
     * @param $abstract
     * @param $concrete
     * @param $shared
     */
    public function bind($abstract, $concrete = null, $shared = false){
        App::bind($abstract, $concrete, $shared);
    }

    /**
     * 提供给服务提供者的make方法封装
     * @param $abstract
     * @param $parameters
     */
    public function make($abstract, $parameters = []){
        return App::make($abstract, $parameters);
    }

    /**
     * 提供给服务提供者的单例绑定封装
     * @param $abstract
     * @param $concrete
     * @param $shared
     */
    public function singleton($abstract, $concrete, $shared = true){
        App::singleton($abstract, $concrete, $shared);
    }



}