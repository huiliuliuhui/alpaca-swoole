<?php
/**
 * 服务提供者注册配置
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/21 0021
 * Time: 下午 23:23
 */

$app = [
    'providers' =>[
        App\Providers\RouterServiceProvider::class,
        App\Providers\ConfigServiceProvider::class,
        App\Providers\LogServiceProvider::class,
    ],
];


return $app;