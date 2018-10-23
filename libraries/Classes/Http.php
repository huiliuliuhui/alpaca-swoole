<?php
namespace Classes;

use Classes\Log;

class Http
{
	/**
	 * 发送接口请求
	 * @param string $url
	 * @param array $params
	 * @param string $type
	 * @return boolean|mixed
	 */
	public static function sendRequest($url, $params = '', $type = 'get', $header = array() , $timeout = 2){
		$type = strtolower($type);
		$headers = array();
		if($header){
			foreach($header as $key => $val){
				$headers[] = $key.':'.$val;
			}
		}
		
		$curl = curl_init();			
		if($type == 'get'){
			if(strpos($url, '?') === false){
				$url .= "?";
			}			
			$param_str = '';
			if(!empty($params) && is_array($params)){
				foreach ($params as $param_key=>$param_value){
					$param_str .= "&".$param_key."=".$param_value;
				}
			}
			$url .= $param_str;
			curl_setopt($curl, CURLOPT_URL, $url);			
			if(!empty($headers)){
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			}
			curl_setopt($curl, CURLOPT_HEADER, 0);			
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			$return_data = curl_exec($curl);
			
		}else{
			curl_setopt($curl, CURLOPT_URL, $url);
			if(!empty($headers)){
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			}			
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
			$return_data = curl_exec($curl);			
		}
		
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		var_dump($code);
		//$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
		if(intval($code) >= 400){
			//记录日志@todo
			return false;
		}
		
		return $return_data;
	}
	
	/**
	 * 本地环境测试时15张图片,单线程耗时3.3秒 并发时耗时0.5秒
	 * 多进程发起请求,获取数据
	 * @param array $urlarr url数组
	 * @param string $timeout 单个进程超时时间
	 * @return multitype:
	 */
	public static function curlMultiFetch($urlarr = array(),$timeout = 30)
	{
		$nch = 0;
		$mh = curl_multi_init ();
		foreach ($urlarr as $url) {
			$ch [$nch] = curl_init ();
			curl_setopt_array ( $ch [$nch], array (
					CURLOPT_URL => $url,
					CURLOPT_HEADER => false,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => $timeout
			) );
			curl_multi_add_handle ( $mh, $ch [$nch] );
			++ $nch;
		}

		do {
			$mrc = curl_multi_exec ( $mh, $running );
		} while ( CURLM_CALL_MULTI_PERFORM == $mrc );

		while ( $running && $mrc == CURLM_OK ) {
			if (curl_multi_select ( $mh, 0.5 ) > - 1) {
				do {
					$mrc = curl_multi_exec ( $mh, $running );
				} while ( CURLM_CALL_MULTI_PERFORM == $mrc );
			}
		}

		//@todo 记录日志
		if ($mrc != CURLM_OK) {			
			error_log ( "CURL Data Error" );
		}

		$nch = 0;
		foreach ( $urlarr as $url ) {
			$err = curl_error ( $ch [$nch] );
			if ($err == '') {
				$result [$url] = curl_multi_getcontent ( $ch [$nch] );
			} else {
				//@todo 记录日志
				error_log ( "curl error".$err );
			}
			curl_multi_remove_handle ( $mh, $ch [$nch] );
			curl_close ( $ch [$nch] );
			++ $nch;
		}

		curl_multi_close ($mh);
		return $result;
	}
}