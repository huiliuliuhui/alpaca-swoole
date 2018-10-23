<?php
namespace Classes;
/**
 * @copyright (c) 2017 发送邮件类
 * @file 发送邮件类
 * @brief 发送邮件类
 * @author liuzhenbang
 * @date 2017/06/12 22:09:43
 * @version 1.0.0
 */
class Email
{
	//文件名
	private $config_value = [
	    'url'=>'api.kuaizitech.com/service/send_email/send',
        'app_id'=>3,
        'app_key'=>'ew9!@HCy8S3xid8MZ',//pro
        'company'=>'qdtech',//公司参数
//        'app_key'=>'SqQZ%&ew9!2DDQUjee',//dev
//        'url'=>'dev.api.kuaizitech.com/service/send_email/send',
    ];

	//开始下载
    /*
     * $post_data  json格式，to 字符串，多个邮箱以","间隔，subject 标题，message，内容，支持HTML，from 发送人名
     * $post_data = "{\"to\":\"562398577@qq.com\",\"subject\":\"邮件标题\",\"message\":\"邮件内容\"}";
     *
     */
	public function send_email($post_data)
	{
	    $token=$this->generate_access_token($this->config_value['app_id'],$this->config_value['app_key'],0);
        $this->config_value['url'].='?token='.$token;
//        $post_data = "{\"to\":\"562398577@qq.com\",\"subject\":\"邮件标题\",\"message\":\"邮件内容\","from":"快决测"}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config_value['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output,true);
	}

    function generate_access_token($app_id, $app_key, $advertiser_id)
    {
        $time = time();
        $sign = sha1($time . $advertiser_id . $app_key);
        $token = base64_encode("{$time},{$advertiser_id},{$app_id},{$sign}");
        return $token;
    }

    //开始下载
    /*
     * $post_data  json格式，to 字符串，多个邮箱以","间隔，subject 标题，message，内容，支持HTML，from 发送人名
     * $post_data = "{\"to\":\"562398577@qq.com\",\"subject\":\"邮件标题\",\"message\":\"邮件内容\"}";
     *
     */
    public function async_send_email($post_data)
    {
        $token=$this->generate_access_token($this->config_value['app_id'],$this->config_value['app_key'],0);
        $this->config_value['url'].='?token='.$token;
//        $post_data = "{\"to\":\"562398577@qq.com\",\"subject\":\"邮件标题\",\"message\":\"邮件内容\","from":"快决测"}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config_value['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
        curl_setopt($ch, CURLOPT_NOSIGNAL,true);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        if($_GET['debug_email']==1){
            var_dump($ch);
        }
        return json_decode($output,true);
    }
}