<?php
namespace Classes;
use Classes\Db;
use Classes\Cache;
use Classes\IFilter;
use \Common\Api;
use \Common\Base;

if(!defined('KZ_CORE')) exit('Access denied');
/**
 * Author: wulianwang
 * Date: 2017/05/15
 * Time: 9:33
 * Copyright @ kuaizi
 */

class SurveyData extends Base
{
	public $user_ip_key = "user_ip_";
	//public

	public function __construct()
	{

	}


	public function indexAction()
	{
		echo 'hello world ! ';
		echo PHP_INT_MAX;
    }

	/**
	 * @use 问卷访问visit请求数据收集
	 */
	public function visitSurveyAction(){
		$request = parent::getRequest();
		$survey_id = IFilter::act($request->getRequest("survey_id"),"int");
		$target = IFilter::act($request->getRequest("target"));
		$kz_user_id = $this->_generateUserId();//生成用户id
		$is_answer = $this->_checkUserIsAnswer($survey_id); //判断用户是否回答过问卷
		if($is_answer){$this->apiReturn(1,array("kz_user_id"=>$kz_user_id),"用户答过问卷");}
		$redis = new Redis();
		$answer_user_info = array();
		$answer_user_info['start_answer_time'] = time();//开始答题时间
		$answer_user_info['target'] = $target;//是否目标人群  -1:未知 0:否  1:是
		$answer_user_info['answer_max_question'] = -1;//已经回答的最后一题id
		$answer_user_info['finish'] = 0;//是否已经完成答题 0:未完成  1:已完成
		$answer_user_info['end_answer_time'] = -1;//结束答题时间
		$answer_user_info['ip'] = get_ip(); //ip地址
		$answer_user_info_json_encode = @json_encode($answer_user_info,JSON_UNESCAPED_UNICODE);
		$user_key = "survey_answer_user_{$survey_id}";
		$redis->hSet($user_key, $kz_user_id, $answer_user_info_json_encode);
		$this->apiReturn(0,array("kz_user_id"=>$kz_user_id),"添加访问数据成功");
	}


	/**
	 * @use 储存用户答案
	 */
	public function answerSurveyAction(){
		$request = parent::getRequest();
		$survey_id = IFilter::act($request->getRequest("survey_id"));
		$kz_user_id = IFilter::act($request->getRequest("kz_user_id"));
		$question_type = IFilter::act($request->getRequest("question_type"));
		$show_type = IFilter::act($request->getRequest("show_type"));
		$answer = IFilter::act($request->getRequest("answer"));
		$question_id = IFilter::act($request->getRequest("question_id"));

		$redis = new Redis();
		$user_key = "survey_answer_user_{$survey_id}";
		$user_hkey = $kz_user_id;
		$answer_user_info_cache_val = $redis->hGet($user_key, $user_hkey);
		$answer_user_info_json = !empty($answer_user_info_cache_val) ? @json_decode($answer_user_info_cache_val,true) : array();
		$old_answer_max_question = get_value_with_default("answer_max_question", $answer_user_info_json, -1);
		if($question_id > $old_answer_max_question){
			$answer_user_info_json['answer_max_question'] = $question_id;//题目id
		}
		$question_answer_time_key = "q".$question_id."_answer_time";
		$answer_user_info_json[$question_answer_time_key] = time();//每一题的答题上报时间
		$start_answer_time = get_value_with_default("start_answer_time", $answer_user_info_json, -1);
		if($start_answer_time <= 0){
			$answer_user_info_json['start_answer_time'] = time();
		}
		//计算当前题目的答题耗时
		$last_update_time = @$answer_user_info_json['last_update_time'];
		$last_update_time = !empty($last_update_time) && is_numeric($last_update_time) ? $last_update_time : time();
		$question_use_time_key = "q".$question_id."_use_time";
		$current_answer_use_time = time() - $last_update_time;
		$answer_user_info_json[$question_use_time_key] = $current_answer_use_time;
		$answer_user_info_json['last_update_time'] = time();

		//更新用户信息
		$answer_user_info_json_encode = @json_encode($answer_user_info_json,JSON_UNESCAPED_UNICODE);
		$redis->hSet($user_key, $user_hkey, $answer_user_info_json_encode);

		//记录答案到缓存
		$answer_key = "survey_answer_data_{$survey_id}";
		$answer_hkey = $kz_user_id;
		$answer_user_data_cache_val = $redis->hGet($answer_key, $answer_hkey);
		$answer_user_data_json = !empty($answer_user_data_cache_val) ? @json_decode($answer_user_data_cache_val, true) : array();
		$answer_user_data_json[$question_id] = $answer;
		$answer_user_data_json["show_type"] = $show_type;
		$answer_user_data_json["question_type"] = $question_type;
		$answer_user_data_json_encode = @json_encode($answer_user_data_json,JSON_UNESCAPED_UNICODE);
		$redis->hSet($answer_key, $answer_hkey, $answer_user_data_json_encode);
		$this->apiReturn(0,array("kz_user_id"=>$kz_user_id),"添加数据成功");
	}

	/**
	 * @use 问卷回答结束
	 */
	public function finishSurveyAction(){
		$request = parent::getRequest();
		$survey_id = IFilter::act($request->getRequest("survey_id"),"int");
		$kz_user_id = IFilter::act($request->getRequest("kz_user_id"));
		$redis = new Redis();
		//更新用户的答题状态信息
		$user_key = "survey_answer_user_{$survey_id}";
		$user_hkey = $kz_user_id;
		$answer_user_info_cache_val = $redis->hGet($user_key, $user_hkey);
		$answer_user_info_json = !empty($answer_user_info_cache_val) ? @json_decode($answer_user_info_cache_val,true) : array();
		$answer_user_info_json['finish'] = 1;
		$answer_user_info_json['end_answer_time'] = time();//结束答题时间
		$this->apiReturn(0,array("kz_user_id"=>$kz_user_id),"添加数据成功");
	}
	
	//public function
	

	/**
	 * API_DOC 设置方法传参
	 * @return array
	 */
	public function getRules(){
		
	}

	/**
	 * @use 生成用户id
	 */
	private function _generateUserId(){
		$ip = @get_ip();
		$long=(microtime(true)*10000).ip2long($ip);
		$kz_user_id=base_convert($long, 10, 36);
		return $kz_user_id;
	}

	/**
	 * @use 判断用户是否回答过问卷
	 * @param $survey_id int 问卷id
	 * @return  boolean
	 */
	private function _checkUserIsAnswer($survey_id){
		$ip = get_ip();
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$redis = new Redis();
		$is_answered = $redis->sAdd($this->user_ip_key.$survey_id, $ip . $browser);
		return $is_answered;
	}

	/**
	 * @use 判断生成token是否失效
	 * @param $survey_id
	 */
	private function _checkTokenValid($survey_id){

	}
}