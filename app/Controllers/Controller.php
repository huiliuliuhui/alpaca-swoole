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
    public $serv = null;
    public $data = [];
    public $redis = null;
    public $log = null;


    function __construct()
    {
        $this->redis = App::make("redis");
        $this->log = App::make("Log");
    }

}