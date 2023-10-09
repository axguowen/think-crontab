# ThinkPHP 定时任务扩展

一个简单的ThinkPHP定时任务扩展，定时功能基于Workerman4.1开发

支持多任务执行

支持定时执行闭包函数

支持定时执行类的动态方法

支持定时执行类的静态方法

执行器方法均支持依赖注入

## 安装

~~~
composer require axguowen/think-crontab
~~~

## 配置

首先配置config目录下的crontab.php配置文件。
配置项说明：

~~~php
return [
    // 任务列表
    'tasks' => [
        [
            // 任务名称
            'name' => '执行闭包函数',
            // 间隔时间, 单位: 秒
            'interval' => 2,
            // 执行器，支持闭包、类的动态方法、类的静态方法，支持参数依赖注入
            'handler' => function(\think\App $app){
                var_dump($app->version() . 'aaabbb');
            }
        ],
        [
            'name' => '执行类的静态方法',
            'interval' => 4,
            'handler' => \app\crontab\Handler::class . '::staticMethod',
        ],
        [
            'name' => '执行类的动态方法',
            'interval' => 6,
            // 这里实例化Handler类后执行publicMethod方法
            'handler' => [\app\crontab\Handler::class, 'publicMethod'],
        ],
        [
            'name' => '不指定方法则默认执行类的handler方法',
            'interval' => 8,
            // 这里实例化Handler类后执行handler方法，请确保Handler类中存在handler方法
            'handler' => \app\crontab\Handler::class,
        ],
    ],
    // 是否以守护进程启动
    'daemonize' => false,
    // 内容输出文件路径
    'stdout_file' => '',
    // pid文件路径
    'pid_file' => '',
    // 日志文件路径
    'log_file' => '',
    // Worker配置
    'worker' => [
        // 进程名称
        'name' => 'think-crontab',
        // 进程数量
        'count' => 5,
        // 支持workerman的其它配置参数
    ],
];
~~~

## 启动停止

定时任务的启动停止均在命令行控制台操作，所以首先需要在控制台进入tp目录

### 启动命令

~~~
php think crontab start
~~~

要使用守护进程模式启动可以将配置项deamonize设置为true
或者在启动命令后面追加 -d 参数，如下：
~~~
php think crontab start -d
~~~

### 停止
~~~
php think crontab stop
~~~

### 查看进程状态
~~~
php think crontab status
~~~

## 注意
Windows下不支持多进程设置，也不支持守护进程方式运行，正式生产环境请用Linux