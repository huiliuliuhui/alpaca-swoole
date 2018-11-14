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
    private $config = [];

    function __construct($config = [])
    {
        if (!empty($config)){
            $this->config = $config;
        }

    }



    /**
     * @param $level
     * @param $msg
     */
    private  function writeLog($level, $msg)
    {
        if (is_array($msg) || is_object($msg)){
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        $date = date('Y-m-d H:i:s', time());
//        $msg = sprintf('%s|%s|%s|%s', $date, $this->getTraceId(), $level, $msg) . PHP_EOL;//时间|traceId|日志级别|日志信息
        $msg = sprintf('%s|%s|%s', $date, $level, $msg) . PHP_EOL;//时间|日志级别|日志信息
        //目前只打印到控制台：用于cli运行流程监控
        if (DEBUG_CLI == true) {
            echo $msg;
        }
        if (!$this->checkLevel($level)) return;
        if ($this->getConfig('drive') === 'file') {
            $path = $this->getConfig('path');
            $file = $path . '/' .  $this->getFormateDate() ."-". $this->getConfig('name') ;//日志文件名称：20181011-myapp.log.
            if (!is_dir($path))
                @mkdir($path, 0755, true);

            error_log($msg, 3, $file);
        }
    }


    /**
     * @param $msg
     */
    public  function info($msg)
    {
        $this->writeLog('Info', $msg);
    }

    /**
     * @param $msg
     */
    public  function debug($msg)
    {
        $this->writeLog('Debug', $msg);
    }

    /**
     * @param $msg
     */
    public  function notice($msg)
    {
        $this->writeLog('Notice', $msg);
    }

    /**
     * @param $msg
     */
    public  function warning($msg)
    {
        $this->writeLog('Warning', $msg);
    }

    /**
     * @param $msg
     */
    public  function fatal($msg)
    {
        $this->writeLog('Fatal', $msg);
    }


//    /**
//     * 获取追踪id
//     * @return string
//     */
//    public  function getTraceId()
//    {
//        if (!defined('TRACE_ID'))
//        {
//            $trace_id = !empty($_REQUEST['debug_trace_id']) ? $_REQUEST['debug_trace_id'] : hash('crc32b', microtime(true));
//            define('TRACE_ID', $trace_id);
//        }
//
//        return TRACE_ID;
//    }

    /**
     * 检查级别是否需写日志
     * @param string $level
     * @return bool
     */
    private  function checkLevel($level = '')
    {
        if (empty($level) || !in_array($level, $this->getRecordLevel())) return false;
        return true;
    }

    /**
     * 获取需记录日志的级别
     * @return array
     */
    private  function getRecordLevel()
    {
        return $this->getConfig('level');
    }

    /**
     * 获取配置
     * @param string $key
     * @return bool|mixed
     */
    private  function getConfig($key = '')
    {
        return $this->config[$key] ?? '';
    }

    private function getFormateDate(){
        return date($this->getConfig('format'), time());
    }







}