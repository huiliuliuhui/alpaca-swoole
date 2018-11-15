## 简介

本框架是一个免费开源的，快速、简单的面向对象的轻量级基于swoole-framework的webim开发框架，在swoole实例的基础上简单地封装了一层容器，实现了依赖注入和控制反转，并将swoole对象反注入到容器中，可以在自己编写的应用程序中简单快速地访问swoole对象。

该框架可以简单快速地构建基于swoole的webim服务器。



## 功能和组件

- 支持mvc
- 命名空间，自动加载-可以方便地引入自定义的命名空间，实现自动加载
- 容器绑定，依赖注入，控制反转-通过容器技术在一定程度上解耦，一切资源可以方便快速地注入到容器，从而在全局方便地调用
- 服务和服务提供者-通过服务和服务提供者的形式，将新模块或中间件注入到应用程序
- 提供数据库和redis服务



## 使用指南

1，该框架运行在linux服务器中，需要php 7.0+，mysql， redis，swoole

2，下载代码

```
git clone https://github.com/top-think/thinkphp.git
```

3，修改配置文件

- [ ] 修改webim.log配置文件

  ```
  $config = [
  
      /**
       * 需要修改
       */
      'server' => [
          'host'   => '0.0.0.0',//监听的HOST
          'port'   => '4092',//监听的端口
      ],
  
  
      /**
       * 根据服务器情况需要修改
       */
      'swoole' => [
          'log_file'        => ROOT_PATH . '/log/swoole.log',
          'worker_num'      => 4,//worker线程数量，一般设为cpu核数的1-2倍
          'max_request'     => 0, //0-表示不会重启worker，不要修改这里，因为webim服务是有状态的服务
          'task_worker_num' => 16,//task线程数量
          'daemonize'       => 0,//是否要作为守护进程
      ],
  
  
      /**
       * 该配置不需要修改
       */
      'webim' => [
          'log_file' => ROOT_PATH . '/log/webim.log',//聊天记录存储的目录
          'send_interval_limit' => 1, //只允许1秒发送一次
      ],
  
  
  
  ];
  ```

  

  根据服务器的实际情况配置监听端口（默认4092端口），worker_num（默认4），task_worker_num（默认16）

  

  - [ ] 配置mysql

    ```
    
    $mysql = [];
    $host = \Swoole::getInstance()->config->config_path[0];
    
    $envs = [
        'dev' => [
            'driver'       => "pdo",
            'type'       => "mysql",
            'hostname'       => "127.0.0.1",
            'hostport'       => 3306,
            'username'       => "root",
            'password'     => "123456",
            'database'       => "kz_decision",
            'charset'    => "utf8",
        ],
    
        'Test' => [
            'driver'       => "pdo",
            'type'       => "mysql",
            'hostname'       => "127.0.0.1",
            'hostport'       => 3306,
            'username'       => "root",
            'password'     => "123456",
            'database'       => "kz_decision",
            'charset'    => "utf8",
        ],
    
        'vagrant_data' => [
            'driver'       => "pdo",
            'type'       => "mysql",
            'hostname'       => "127.0.0.1",
            'hostport'       => 3306,
            'username'       => "root",
            'password'     => "123456",
            'database'       => "kz_decision",
            'charset'    => "utf8",
        ],
    
        'default' => [
            'driver'       => "pdo",
            'type'       => "mysql",
            'hostname'       => "127.0.0.1",
            'hostport'       => 3306,
            'username'       => "root",
            'password'     => "123456",
            'database'       => "kz_decision",
            'charset'    => "utf8",
        ]
    
    ];
    
    foreach ($envs as $env => $config){
        if (strpos($host,$env) !== false || $env == "default")$mysql = $config;
    }
    
    
    return $mysql;
    ```

    

  这里是通过获取项目所在目录来判断环境，当然也可以在linux服务器中配置一个环境变量来判断。上述配置文件的键是目录名，值数组是配置。当然你也可以根据你的实际情况来编写配置文件的获取规则。

- [ ] 配置redis

配置方式同上





## 运行

- 启动swoole服务

```
php webim_server.php start
```

- 使用jmeter测试

  jmeter配置：ip:127.0.0.1, port:4092, protocol:ws, message: {"cmd" : "index", "mod", "user", "data":{"demo":"hello webim"}}

- 查看输出日志

- 启动运行过程中有疑问可以联系我

