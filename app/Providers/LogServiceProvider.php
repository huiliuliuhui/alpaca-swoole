<?php
/**
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/23 0023
 * Time: 上午 9:54
 */

namespace App\Providers;

use App\Services\LogService;
use Kernel\Provider\ServiceProvider;


class LogServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        $this->singleton("Log",function(){
            $configService = $this->make("Config");
            $config = $configService->getConfig();
            $log = $config['mylog'];
            return new LogService($log);
        });

    }

}