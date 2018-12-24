<?php
/**
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/23 0023
 * Time: 下午 12:33
 */

namespace App\Controllers;
use App\Models\UserModel;
use Kernel\App;
use Predis\Client;

class UserController extends Controller
{
    function index(){
        $model = new UserModel();
//        $users = $model->getConnection()->find();
        $client = new Client();
        $client->set('aa', 'xxx');
        $value = $client->get('aa');
        $this->log->info($value);
        $this->setWatchLog();
        App::make("sendMessage", [$this, $this->data['client_id'], json_encode($this->data)]);
    }

}