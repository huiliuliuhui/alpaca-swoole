<?php
namespace Classes;

/**
 * 调试错误模式:
 * 0    =>   非调试模式，不显示异常、错误信息但记录异常、错误信息
 * 1    =>   调试模式，显示异常、错误信息但不记录异常、错误信息
 */
define('DEBUG_ERROR', 0);
require 'WriteLog\baseErrorHandler.class.php';
class RegisterErrorHandler
{
    /**
     * 方  法：注册异常、错误拦截
     * 参  数：void
     * 返  回：void
     */
    public static function register()
    {
        $server_host = @$_SERVER['HTTP_HOST'];
        $server_host = strtolower($server_host);
        if (strpos($server_host, "dev") === false) {
            define('DEBUG_ERROR', 0);
        } else {
            define('DEBUG_ERROR', 1);
        }
        global $argv;
        if(DEBUG_ERROR)
        {//如果开启调试模式
            ini_set('display_errors', 1);
            return;
        }
        //如果不开启调试模式
        ini_set('error_reporting', -1);
        ini_set('display_errors', 0);
        $handler = new \errorHandler();
        $handler->argvs = $argv;//此处主要兼容命令行模式下获取参数
        $handler->register();
    }
}
