<?php
namespace WebIM;
use Swoole;
use Swoole\Filter;
use Kernel\App;

class Server extends Swoole\Protocol\CometServer
{
    protected $redis;
    public $app;

    function __construct($config = array())
    {
        parent::__construct($config);


        //检测webim_log日志目录是否存在


        $this->checkLoger($config);
        echo "检查文件是否存在" . PHP_EOL;


        //注册发送消息方法到容器
        echo "注册发送消息方法到容器" . PHP_EOL;
        $this->registerSendMessage();

        //注册redis单例到容器
        echo "注册redis单例到容器" . PHP_EOL;
        $this->registerRedis();

    }



    function onFinish($serv, $task_id, $data)
    {
        $this->send(substr($data, 0, 32), substr($data, 32));
    }




    /**
     * @param $serv \swoole_server
     */
    function onStart($serv, $worker_id = 0)
    {
        echo "{$worker_id}worker进程启动"   . PHP_EOL;
        parent::onStart($serv, $worker_id);
    }




    /**
     * 任务处理函数
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     */
    function onTask($serv, $task_id, $from_id, $data)
    {

    }



    /**
     * 消息调度中心，收发消息路由
     * @see WSProtocol::onMessage()
     */
    function onMessage($client_id, $ws)
    {
        $this->log("onMessage #$client_id: " . $ws['message']);
        $message = json_decode($ws['message'], true);

        $message['client_id'] = $client_id;

        $router = $this->getRouter();
        $router->callRouter($message, $this);
    }



    /**
     * 下线时，通知所有人
     */
    function onExit($client_id)
    {
    }



    /**
     * 发送错误信息
    * @param $client_id
    * @param $code
    * @param $msg
     */
    function sendErrorMessage($client_id, $code, $msg)
    {
        $this->sendJson($client_id, array('cmd' => 'error', 'code' => $code, 'msg' => $msg));
    }

    /**
     * 发送JSON数据
     * @param $client_id
     * @param $array
     */
    function sendJson($client_id, $array)
    {
        $msg = json_encode($array);

        if ($this->send($client_id, $msg) === false)
        {
            $this->close($client_id);
        }

    }


    /**
     * @param $to_id
     * @param $msg
     * @use 群发
     */
    function broadcast($to_id, $msg)
    {
        $msg = json_encode($msg);

        if(empty($to_id)){
            //to do
            $this->log('to_id为空 # ' . $msg);
            return ;
        }
        $client_ids = $to_id;
        foreach($client_ids as $client_id){
            $this->send((int)$client_id, $msg);
        }
    }



    /**
     * 检查日志文件是否存在，如果不存在，则创建
     * @param $config
     * @throws \Exception
     */
    private function checkLoger($config)
    {
        $log_dir = dirname($config['webim']['log_file']);
        if (!is_dir($log_dir))
        {
            mkdir($log_dir, 0777, true);
        }
        if (!empty($config['webim']['log_file']))
        {
            $logger = new Swoole\Log\FileLog($config['webim']['log_file']);
        }
        else
        {
            $logger = new Swoole\Log\EchoLog(true);
        }
        $this->setLogger($logger);   //Logger
    }

    private function registerSendMessage(){
        App::singleton("sendMessage", function($serv, $id, $data){
            $this->sendJson($id, $data);
        });
    }

    private function registerRedis(){
        App::singleton("redis", function(){
            return \Swoole::getInstance()->redis;
        });
    }

    private function  getRouter(){
        return App::make("Router");
    }

}

