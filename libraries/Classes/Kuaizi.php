<?php
namespace Classes;

use Classes\Http;

class Kuaizi
{
	
	public static $gateway = "http://micro.kuaizi.co/dev_logo_cut/";
	
	/*
	* @ 多线程图片去logo
	* @param $image_array array 图片地址
	* @return array
	*/
	public static function logoEraseMultiThread($image_array)
	{
		$tmp = array();
		foreach($image_array as $row){
			$tmp[] = array('input' => $row, 'output' => substr($row,0, strrpos($row, '.')) . '_clear' . substr($row, strrpos($row, '.')));
		}
		$string = json_encode($tmp);
		$new_image_erase_string = $string;

		$params = array();
		$params['data'] = $new_image_erase_string;
		$data = self::sendRequest("logo_erase?", $params);
		if($data['errno'] > 0){
			return false;
		}
		$obj_array = @json_decode($data['body']);
		$output_image_array = array();
		foreach($tmp as $image){
			$output_image_array[] = $image["output"];
		}
		
		return $output_image_array;
	}
	
	/*
	* @ 获取蒙版内接最大矩形
	* @param string $url 蒙版图片地址
	* @return array {"x0":68,"y0":68,"x1":235,"y1":233} 左上角、右下角坐标
	*/
	public static function getMaskInRect($url)
	{
		if(strpos($url, '/data0') !== false && strpos($url, '/data0') == 0){
			$path = $url;
		}elseif(strpos($url, 'http') !== false){
			$path = \Helper::urlToLocation($url);
		}else{
			$path = '/data0/moosefs/client/mask/'. ltrim($url, '/');
		}
		
		$param = array();
		$param['path'] = $path;
		$data = self::sendRequest("max_rectangle?", $param);
		if($data['errno'] > 0){
			return false;
		}
		$res = @json_decode($data['body'], true);
		if($res['code'] == 0){
			return $res['data'];
		}
		
		return false;
	}
	
	
	/*
	* @ 获取图片主体信息（左上角坐标pos_x pos_y, 主体尺寸 size_x size_y, 背景色等）
	* @param array $images  图片地址
	* @return array | boolean
	*/
	public static function getImagesBody($images)
	{
		$param = array();
		$data = array();
		$is_array = true;
		if(!is_array($images)){
			$is_array = false;
			$images = array($images);
		}
		foreach($images as $img){
			if(stripos($img, '/data0') !== false && stripos($img, '/data0') == 0){
				$img = $img;
			}elseif(strpos($url, 'http') !== false){
				$img = \Helper::urlToLocation($img);
			}else{
				$img = '/data0/moosefs/client/'. ltrim($img, '/');
			}
			$data[] = $img;
		}
		
		$param['data'] = json_encode($data);
		$data = self::sendRequest("main_region_identify?", $param);
		if($data['errno'] > 0){
			return false;
		}
		$res = @json_decode($data['body'], true);
		if($res['code'] == 0 && $res['data']){
			if($is_array){
				return $res['data'];
			}else{
				return $res['data'][0];
			}
		}
		
		return false;
	}
	
	public function getImageColor($images)
	{
		try{
			$data = array();
			foreach($images as $img){
				if(stripos($img, '/data0') !== false && stripos($img, '/data0') == 0){
					$img = $img;
				}elseif(strpos($url, 'http') !== false){
					$img = \Helper::urlToLocation($img);
				}else{
					$img = '/data0/moosefs/client/'. ltrim($img, '/');
				}
				$data[] = $img;
			}
			//echo "<pre>";
			//print_r($data);
			$param = array();
			$param['data'] = json_encode($data);
			$param['time'] = microtime ( true ) * 1000;
			$data = self::sendRequest("color_tags_identify?", $param);
			if($data['errno'] > 0){
				return false;
			}
			$res = @json_decode($data['body'], true);
			if($res['code'] != 0 || !$res['data']){
				return false;
			}

			$data_array = $res['data'];
			$i = 0;
			$result_array = array();
			foreach($images as $image){
				$color_data = $data_array[$i];
				$color_tag = array();
				$lightness = array();
				$lightness["value"] = $color_data["lightness"];
				$lightness["tag"] = 0;
				$warmth = array();
				$warmth["value"] = $color_data["warmth"];
				$warmth["tag"] = 0;
				$satura = array();
				$satura["value"] = $color_data["satura"];
				$satura["tag"] = 0;
				$contrast = array();
				$contrast["value"] = $color_data["contrast"];
				$contrast["tag"] = 0;
				$hue = array();
				$hue["value"] = $color_data["hue"];
				$hue["tag"] = 0;
				$color_tag["lightness"] = $lightness;
				$color_tag["warmth"] = $warmth;
				$color_tag["satura"] = $satura;
				$color_tag["contrast"] = $contrast;
				$color_tag["hue"] = $hue;
				$result_array[$image] = $color_tag;

				$i += 1;
			}
		}catch(Exception $e){
			echo "<pre>";
			print_r($e);
		}

		
		return $result_array;
	}
	
	public static function sendRequest($url, $params = '', $type = 'get', $header = array() , $timeout = 10)
	{
		$url = "key-color-api?";
		$data = array("path"=>"/data0/moosefs/client/qdtech/decision/dd/56/dd567af12db81a78fd7a30f438134392.png");
		$params = json_encode($data);
		//$config = \Yaf\Registry::get("config");
		$config = \Yaf\Application::app()->getConfig();
		$url = "http://192.168.10.50:4020/api";
		echo $url;
		$return = Http::sendRequest($url, $params, "post", $header, $timeout);
		print_r($return);
		return $return;
	}
}
