<?php
/**
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/19 0019
 * Time: 下午 17:21
 */

namespace Kernel\Container;


class Container
{
    /**
     *  容器绑定，用来装提供的实例或者 提供实例的回调函数
     * @var array
     */
    static $building = [];//注册数组
    static $instances = [];//单例模式对象缓存

    /**
     * 注册一个绑定到容器
     */
    static function bind($abstract, $concrete = null, $shared = false)
    {
        if(is_null($concrete)){
            $concrete = $abstract;
        }
        if(!$concrete instanceOf \Closure){
            $concrete = self::getClosure($abstract, $concrete);
        }
        self::$building[$abstract] =  compact("concrete", "shared");
    }

    /**
     * 注册一个共享的绑定 单例
     * @param $abstract
     * @param $concrete
     * @param bool $shared
     */
    static function singleton($abstract, $concrete, $shared = true){
        self::bind($abstract, $concrete, $shared);
    }

    /**
     * 默认生成实例的回调闭包
     *
     * @param $abstract
     * @param $concrete
     * @return \Closure
     */
    static function getClosure($abstract, $concrete)
    {
        return function($c) use($abstract, $concrete){
            $method = ($abstract == $concrete)? 'build' : 'make';
            return $c::$method($concrete);
        };
    }

    /**
     * 生成实例
     */
    static function make($abstract, $parameters = [])
    {

        $concrete = self::getConcrete($abstract);

        if(self::isBuildable($concrete, $abstract)){
            $object = self::build($concrete, $parameters);
        }else{
            $object = self::make($concrete);
        }


        //如果在instances数组中有该对象，直接返回，响应单例模式
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }

        //如果设置了单列模式，将该对象缓存到instances数组
        if(self::isShared($abstract)){
            self::$instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * 判断是否是单例共享模式的对象
     * @param $abstract
     * @return mixed
     */
    static function isShared($abstract){
        if (isset(self::$building[$abstract])){
            $ob = self::$building[$abstract];
            return $ob['shared'];
        }else{
            return false;
        }

    }

    /**
     * 获取绑定的回调函数
     */
    static function getConcrete($abstract)
    {
        if(! isset(self::$building[$abstract])){
            return $abstract;
        }

        return self::$building[$abstract]['concrete'];
    }

    /**
     * 判断 是否 可以创建服务实体
     * @param $concrete
     * @param $abstract
     * @return bool
     */
    static function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof \Closure;
    }

    /**
     * 根据实例具体名称实例具体对象
     * @param $concrete
     * @param $parameters
     * @return mixed|object
     * @throws \ReflectionException
     */
    static function build($concrete, $parameters)
    {
        if($concrete instanceof \Closure){
            if(empty($parameters)){
                return $concrete(self::class);
            }elseif(is_array($parameters)){
                return call_user_func_array($concrete, $parameters);
            }elseif (is_string($parameters)){
                return $concrete(self::class, $parameters);
            }
        }

        //创建反射对象
        try{
            $reflector = new \ReflectionClass($concrete);
        }catch (\Exception $e){
            throw $e;
        }

        if( ! $reflector->isInstantiable()){
            //抛出异常
            throw new \Exception('无法实例化');
        }

        $constructor = $reflector->getConstructor();
        if(is_null($constructor)){
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instance = self::getDependencies($dependencies);
        return $reflector->newInstanceArgs($instance);

    }

    //通过反射解决参数依赖
    static function getDependencies(array $dependencies)
    {
        $results = [];
        foreach( $dependencies as $dependency ){
            $results[] = is_null($dependency->getClass())
                ?self::resolvedNonClass($dependency)
                :self::resolvedClass($dependency);
        }

        return $results;
    }

    /**
     * 解决一个没有类型提示依赖
     * @param \ReflectionParameter $parameter
     * @return mixed
     * @throws \Exception
     */
    static function resolvedNonClass(\ReflectionParameter $parameter)
    {
        if($parameter->isDefaultValueAvailable()){
            return $parameter->getDefaultValue();
        }
        throw new \Exception('出错');

    }

    /**
     * 通过容器解决依赖
     * @param \ReflectionParameter $parameter
     * @return mixed|object
     */
    static function resolvedClass(\ReflectionParameter $parameter)
    {
        return self::make($parameter->getClass()->name);

    }

}