<?php
/**
 * ID 生成策略
 * 毫秒级时间41位+机器ID 10位+毫秒内序列12位。
 * 	0           41     51     64
	+-----------+------+------+
	|time       |pc    |inc   |
	+-----------+------+------+
 *  前41bits是以微秒为单位的timestamp。
 *  接着10bits是事先配置好的机器ID。
 *  最后12bits是累加计数器。
 *  macheine id(10bits)标明最多只能有1024台机器同时产生ID，sequence number(12bits)也标明1台机器1ms中最多产生4096个ID，
 *
 */
namespace Classes;

class Idwork
{
    private static $workerId = 1;
    private static $twepoch = 1451577600000000; //1483200000000;
    private static $sequence = 0;
    private static $maxWorkerId = 15;
    private static $workerIdShift = 10;
    private static $timestampLeftShift = 14;
    private static $sequenceMask = 1023;
    private static $lastTimestamp = -1;

    public static function timeGen()
	{
        //获得当前时间戳
        $time = explode(' ', microtime());
        $time2= substr($time[0], 2);
        return $time[1].$time2;
    }
	
    public static function tilNextMillis($lastTimestamp)
	{
        $timestamp = self::timeGen();
        while ($timestamp <= $lastTimestamp){
            $timestamp = self::timeGen();
        }
		
        return $timestamp;
    }

    public static function nextId($workId = 1)
    {
		if($workId > self::$maxWorkerId || $workId < 0 ){
            throw new \Yaf\Exception("worker Id can't be greater than 15 or less than 0");
        }
		
        self::$workerId = $workId;
		
        $timestamp = self::timeGen();
        if(self::$lastTimestamp == $timestamp) {
            self::$sequence = (self::$sequence + 1) & self::$sequenceMask;
            if (self::$sequence == 0) {
				$timestamp = self::tilNextMillis(self::$lastTimestamp);
            }
        } else {
            self::$sequence  = 0;
        }
        if ($timestamp < self::$lastTimestamp) {
            throw new \Yaf\Excwption("Clock moved backwards.  Refusing to generate id for ".(self::$lastTimestamp-$timestamp)." milliseconds");
        }
        self::$lastTimestamp  = $timestamp;
		$nextId = ((sprintf('%.0f', $timestamp) - sprintf('%.0f', self::$twepoch)) << self::$timestampLeftShift) | ( self::$workerId << self::$workerIdShift ) | self::$sequence;
        return abs($nextId);
    }
}
