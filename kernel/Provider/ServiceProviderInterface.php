<?php

/**
 * 定义服务提供者的规范
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/22 0022
 * Time: 下午 18:32
 */
namespace Kernel\Provider;

interface ServiceProviderInterface
{


    /**
     * 注册到容器
     * @return mixed
     */
    public function register();


    /**
     * 引导方法
     * @return mixed
     */
    public function boot();


}