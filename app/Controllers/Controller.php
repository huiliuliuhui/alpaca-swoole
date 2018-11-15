<?php
/**
 * 控制器基类
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/23 0023
 * Time: 下午 12:34
 */

namespace App\Controllers;
use Kernel\App;

class Controller
{
    public $serv = null;//swoole实例

    public $data = [];//数据

    public $redis = null;//redis实例

    public $log = null;//日志实例

    public $memeory_old = null;//起始内存使用值

    public $time_old = null;//路由起始时间


    function __construct()
    {
        $this->redis = App::make("redis");
        $this->log = App::make("Log");
    }

    /**
     *
     */
    function setWatchLog(){
        $runtime    = number_format(microtime(true) - $this->time_old, 10);
        $reqs       = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $time_str   = ' [运行时间：' . number_format($runtime, 6) . 's][吞吐率：' . $reqs . 'req/s]';
        $memory_use = number_format((memory_get_usage() - $this->memeory_old) / 1024, 2);
        $memory_str = ' [内存消耗：' . $memory_use . 'kb]';
        $this->log->info($time_str . $memory_str);
    }


}