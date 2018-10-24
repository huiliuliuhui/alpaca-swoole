<?php
/**
 * 获取配置文件参数服务
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/23 0023
 * Time: 上午 10:00
 */

namespace App\Services;


class ConfigService
{

    public $config = [];
    private $configPath = __DIR__ . "/../../configs/";
    public $files = [];

    public function getConfig(){

        $this->files = $this->scan_dir($this->configPath);

        $this->parsConfig();

        return $this->config;
    }



    public function parsConfig(){
        foreach ($this->files as $file){
            $fileName = substr($file,0,strpos($file, '.'));

            $config = require $this->configPath . $file;

            $this->config[$fileName] = $config;
        }
    }



    public function scan_dir($dir) {
        $files = array();

        if(@$handle = opendir($dir)) {
            //注意这里要加一个@，不然会有warning错误提示：）
            while(($file = readdir($handle)) !== false) {
                if($file != ".." && $file != ".") {
                    //排除根目录；
                    if(is_dir($dir."/".$file)) {
                        //如果是子文件夹，就进行递归
                        $files[$file] = $this->scan_dir($dir."/".$file);
                    } else {
                        //将文件的名字存入数组
                        $files[] = $file;
                    }

                }
            }

            closedir($handle);

            return $files;
        }
    }

}