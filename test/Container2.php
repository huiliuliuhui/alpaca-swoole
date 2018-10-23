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
    public $building = [];//注册数组
    public $instances = [];//单例模式对象缓存

    /**
     * 注册一个绑定到容器
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if(is_null($concrete)){
            $concrete = $abstract;
        }
        if(!$concrete instanceOf \Closure){
            $concrete = $this->getClosure($abstract, $concrete);
        }
        $this->building[$abstract] =  compact("concrete", "shared");
    }

    /**
     * 注册一个共享的绑定 单例
     * @param $abstract
     * @param $concrete
     * @param bool $shared
     */
    public function singleton($abstract, $concrete, $shared = true){
        $this->bind($abstract, $concrete, $shared);
    }

    /**
     * 默认生成实例的回调闭包
     *
     * @param $abstract
     * @param $concrete
     * @return \Closure
     */
    public function getClosure($abstract, $concrete)
    {
        return function($c) use($abstract, $concrete){
            $method = ($abstract == $concrete)? 'build' : 'make';
            return $c->$method($concrete);
        };
    }

    /**
     * 生成实例
     */
    public function make($abstract, $parameters = [])
    {

        $concrete = $this->getConcrete($abstract);

        if($this->isBuildable($concrete, $abstract)){
            $object = $this->build($concrete, $parameters);
        }else{
            $object = $this->make($concrete);
        }


        //如果在instances数组中有该对象，直接返回
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        //如果设置了单列模式，将该对象缓存到Instances数组
        if($this->isShared($abstract)){
            $this->instances[$abstract] = $object;
        }


        return $object;
    }

    /**
     * 判断是否是单例共享模式的对象
     * @param $abstract
     * @return mixed
     */
    public function isShared($abstract){
        if (isset($this->building[$abstract])){
            $ob = $this->building[$abstract];
            return $ob['shared'];
        }else{
            return false;
        }

    }

    /**
     * 获取绑定的回调函数
     */
    public function getConcrete($abstract)
    {
        if(! isset($this->building[$abstract])){
            return $abstract;
        }

        return $this->building[$abstract]['concrete'];
    }

    /**
     * 判断 是否 可以创建服务实体
     */
    public function isBuildable($concrete, $abstract)
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
    public function build($concrete, $parameters)
    {
        if($concrete instanceof \Closure){
            if(empty($parameters)){
                return $concrete($this);
            }elseif(is_array($parameters)){
                return call_user_func_array($concrete, $parameters);
            }elseif (is_string($parameters)){
                return $concrete($this, $parameters);
            }
        }


        //创建反射对象
        $reflector = new \ReflectionClass($concrete);
        if( ! $reflector->isInstantiable()){
            //抛出异常
            throw new \Exception('无法实例化');
        }

        $constructor = $reflector->getConstructor();
        if(is_null($constructor)){
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instance = $this->getDependencies($dependencies);
        return $reflector->newInstanceArgs($instance);

    }

    //通过反射解决参数依赖
    public function getDependencies(array $dependencies)
    {
        $results = [];
        foreach( $dependencies as $dependency ){
            $results[] = is_null($dependency->getClass())
                ?$this->resolvedNonClass($dependency)
                :$this->resolvedClass($dependency);
        }

        return $results;
    }

    //解决一个没有类型提示依赖
    public function resolvedNonClass(\ReflectionParameter $parameter)
    {
        if($parameter->isDefaultValueAvailable()){
            return $parameter->getDefaultValue();
        }
        throw new \Exception('出错');

    }

    //通过容器解决依赖
    public function resolvedClass(\ReflectionParameter $parameter)
    {
        return $this->make($parameter->getClass()->name);

    }

}