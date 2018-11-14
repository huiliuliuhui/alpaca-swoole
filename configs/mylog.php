<?php
/**
 * 应用程序日志配置文件， App\Providers\LogServiceProvider::class的配置文件
 * Created by 刘辉.
 * User: all
 * Date: 2018/11/14 0014
 * Time: 下午 15:28
 */

$log = [
    'drive' => 'file',
    'path' => ROOT_PATH . '/log',//日志目录
    'name' => 'myapp.log',//日志名称
    'format' => "Y-m-d",//日志前缀
    'level' => ["Info","Debug","Fatal","Notice", "Warning"],//允许的日志消息类型级别
];

return $log;