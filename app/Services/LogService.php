<?php
/**
 * 日志类
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/23 0023
 * Time: 下午 15:50
 */

namespace App\Services;
use Classes\Exception;
use Swoole\Log\FileLog;

class LogService
{
    private $config = [
        'file' => ROOT_PATH . '/log/myapp.log',
    ];
    private $logfile = null;

    function __construct($config = [])
    {
        if (!empty($config)){
            $this->config = $config;
        }

        $this->logfile = new FileLog($this->config);
    }


    function log($msg){

        if (is_array($msg) || is_object($msg)){
            $msg = json_encode($msg,JSON_UNESCAPED_UNICODE);
        }

        if (!is_string($msg)) {
            throw new \Exception("日志消息不是字符串");
        }else{
            $this->logfile->put($msg);
        }

    }




}