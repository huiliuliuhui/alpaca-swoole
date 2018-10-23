<?php

namespace Classes;

use yaf;

class Cache
{
    protected static $instance = [];
    public static $readTimes   = 0;
    public static $writeTimes  = 0;

//    public static $mysql; //Mysql数据库对象
//    public static $scribe; //Scribe日志对象
//    public static $hdfs; //HDFS日志对象
//    public static $hive; //HIVE对象
//    public static $hbase; //HBASE对象
//    public static $ssdb; //SSDB对象
//    public static $cache; //Cache缓存对象数组
//    public static $files; //Files缓存对象数组
//    public static $redis; //Redis缓存对象数组
//    public static $ports; //Redis缓存端口
//    public static $conns; //连接数
//    public static $querys; //查询数
//    public static $time_start; //开始时间
//    public static $time_curr; //当前时间
//    public static $yac; //YAC缓存对象
//    public static $yac_time; //YAC缓存时间

    /**
     * 操作句柄
     * @var object
     * @access protected
     */
    protected static $handler;

    /**
     * 连接缓存
     * @access public
     * @param array         $options  配置数组
     * @param bool|string   $name 缓存连接标识 true 强制重新连接
     * @return \Classes\Cache\Driver
     */
    public static function connect(array $options = [], $name = false)
    {
        $type = !empty($options['type']) ? $options['type'] : 'File';
        if (false === $name) {
            $name = md5(serialize($options));
        }

        if (true === $name || !isset(self::$instance[$name])) {
            $class = false !== strpos($type, '\\') ? $type : '\\Classes\\Cache\\Driver\\' . ucwords($type);
            if (true === $name) {
                return new $class($options);
            } else {
                self::$instance[$name] = new $class($options);
            }
        }
        self::$handler = self::$instance[$name];
        return self::$handler;
    }

    /**
     * 自动初始化缓存
     * @access public
     * @param array         $options  配置数组
     * @return void
     */
    public static function init(array $options = [])
    {
        if (is_null(self::$handler)) {
            // 自动初始化缓存
            if (!empty($options)) {
//                var_dump($options);exit();
                self::connect($options);
            } else {
				$config = Yaf\Application::app()->getConfig()->cache->toArray();
//                var_dump($config);exit();
                self::connect($config);
            }
        }

//        global $_KZ_GLOBAL;
//        self::$cache = array();
//        self::$files = array();
//        self::$redis = array();
//        self::$ssdb = array();
//        self::$ports = array();
//        self::$conns = array('ports' => 0, 'redis' => 0, 'files' => 0, 'yacs' => 0, 'mysql' => 0, 'scribe' => 0);
//        self::$querys = array('redis' => 0, 'files' => 0, 'yacs' => 0, 'mysql' => 0, 'scribe' => 0);
//        self::$time_start = $_KZ_GLOBAL['time_start'];
//        self::$yac = class_exists('Yac') ? new Yac() : false;
////        var_dump(self::$yac);exit();
//        if (self::$yac) {
//            self::$conns['yacs']++;
//            self::$yac_time = intval(self::$yac->get('redis_update_time'));
//            if (self::$yac_time < 1) { //如果YAC中未设置缓存时间
//                self::$yac_time = 360; //默认缓存360秒
//                self::set_redis_update_time(); //设置缓存更新时间
//            } else {
//                self::$yac_time = 300 + self::$yac_time - time();
//            }
//
//            if (self::$yac_time < 1) { //如果YAC中的缓存时间已经过期
//                self::$yac_time = 60; //缓存60秒，避免0秒永久缓存
//                self::set_redis_update_time(); //重新设置缓存更新时间
//            }
//        }
//        debug("Cache()::__construct()");
    }

    /**
     * 切换缓存类型 需要配置 cache.type 为 complex
     * @access public
     * @param string $name 缓存标识
     * @return \Classes\Cache\Driver
     */
    public static function store($name)
    {
		$config = Yaf\Application::app()->getConfig()->cache->toArray();
        if ('complex' == $config['type']) {
            self::connect($config['cache.' . $name], strtolower($name));
        }
        return self::$handler;
    }

    /**
     * 判断缓存是否存在
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public static function has($name)
    {
        self::init();
        self::$readTimes++;
        return self::$handler->has($name);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存标识
     * @param mixed  $default 默认值
     * @return mixed
     */
    public static function get($name, $default = false)
    {
        self::init();
        self::$readTimes++;
        return self::$handler->get($name, $default);
    }

    /**
     * 写入缓存
     * @access public
     * @param string        $name 缓存标识
     * @param mixed         $value  存储数据
     * @param int|null      $expire  有效时间 0为永久
     * @return boolean
     */
    public static function set($name, $value, $expire = null)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->set($name, $value, $expire);
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public static function inc($name, $step = 1)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->inc($name, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public static function dec($name, $step = 1)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->dec($name, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string    $name 缓存标识
     * @return boolean
     */
    public static function rm($name)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->rm($name);
    }

    /**
     * 清除缓存
     * @access public
     * @param string $tag 标签名
     * @return boolean
     */
    public static function clear($tag = null)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->clear($tag);
    }

    /**
     * 缓存标签
     * @access public
     * @param string        $name 标签名
     * @param string|array  $keys 缓存标识
     * @param bool          $overlay 是否覆盖
     * @return \think\cache\Driver
     */
    public static function tag($name, $keys = null, $overlay = false)
    {
        self::init();
        return self::$handler->tag($name, $keys, $overlay);
    }

    public static function __callStatic($method, $params)
    {
        // 自动初始化数据库
		self::init();
        return call_user_func_array([self::$handler, $method], $params);
    }

    /**
     * 获取IP对应的省份和城市ID
     *
     * @param string $ip IP地址
     * @return array 省份ID和城市ID数组
     */
//    public static function get_ip_area($ip) {
//        $type = 'ip_lib';
//        list($ip1, $ip2, $ip3, $ip4) = explode('.', $ip);
//        $key = "$ip1.$ip2.$ip3"; //C段做key
//        $value = self::get_string($key, $type, 'ttl', array('ttl' => 864000));
//        if (!$value) {
//            return array(0, 0);
//        } elseif (preg_match('/^[\d]+\|[\d]+$/Uis', $value)) {
//            return explode('|', $value);
//        } else {
//            $array = msgpack_unpack($value);
//            debug($array);
//            if (!is_array($array)) {
//                return array(0, 0);
//            } elseif (@$array[$ip4]) {
//                return $array[$ip4];
//            } elseif (@$array[0]) {
//                return $array[0];
//            } else {
//                return array(0, 0);
//            }
//
//        }
//    }


    /**
     * 获取缓存信息
     *
     * @param string $key 缓存键值
     * @param string $type 缓存类型
     * @param string $ttl_type 缓存过期类型 默认为空:ttl=yac_time hour:每小时指定分钟更新 ttl:指定ttl时间(秒)
     * @param array $ttl_option 缓存过期选项 hour: array('minute'=>10) ttl: array('ttl'=>3600)
     * @return string $value 缓存信息 失败返回false
     */
//    public static function get_string($key, $type) {
//        self::init();
//        self::$writeTimes++;
//        $value = self::$handler->get("{$type}_$key",false);
//        if (!$value) {
//            return false;
//        }
//        return $value;
//    }

    /**
     * 设置redis更新时间
     *
     */
//    public static function set_redis_update_time() {
////        $redis = $this->connect_cache('creative_list');
//        $msg = "connect redis error";
//        try {
//            $time = intval(self::$handler->get('redis_update_time'));
//        } catch (Exception $e) {
//            $msg = $e;
//        }
//        if ($time) {
//            self::$yac->set('redis_update_time', $time);
//        } else {
////            $this->connect_scribe();
////            $this->scribe->log('set_redis_update_time_error', array(date("Y-m-d H:i:s"), "$msg"));
//            debug("set redis_update_time error!<br>\n$msg");
//        }
//    }


}
