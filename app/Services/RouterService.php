<?php
/**
 * 路由服务
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/23 0023
 * Time: 上午 10:00
 */

namespace App\Services;


class RouterService
{
    /**
     * 路由拦截数据传输，分发给相应的controller
     * @param $data,数据
     * @param $serv,swoole实例对象
     * @return mixed
     */
    public function callRouter($data, $serv){
        $mod = ucfirst($data['mod']);//首字母转成大写
        $cmd = $data['cmd'];

        $class_name = "App\\Controllers\\" . "{$mod}Controller";

        $php_file_name = "{$mod}Controller.php";
        $php_file_path = ROOT_PATH."/app/Controllers/";
        $php_file = $php_file_path.$php_file_name;

        if(empty($php_file) || !is_file($php_file)){
            echo "访问的文件不存在";
        }
        if(!class_exists($class_name)){
            echo "访问的控制器不存在";
        }

        $api_model = new $class_name();
        $api_model->data = $data;
        $api_model->serv = $serv;
        if(!empty($api_model)){
            if(method_exists($api_model, $cmd)){
                $return = $api_model->$cmd();
                return $return;
            }else{
                echo "访问的方法出错";
            }
        }else{
            echo "非法访问";
        }
    }

}