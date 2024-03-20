<?php
// +----------------------------------------------------------------------
// | ThinkPHP Crontab [Simple Crontab Extend For ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkPHP 定时任务扩展
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

return [
	// 计划任务Worker实例名称
	'name' => 'think-crontab',
    // 是否以守护进程启动
	'daemonize' => false,
	// 内容输出文件路径
	'stdout_file' => '',
	// pid文件路径
	'pid_file' => '',
	// 日志文件路径
	'log_file' => '',
    // 任务列表
    'task_list' => [
        [
			// 类型为普通任务
			'type' => 'task',
			// 任务名称
			'name' => 'task_name',
			// 执行周期，单位秒
			'interval' => 5,
			// 执行器，支持闭包、类的动态方法、类的静态方法，支持参数依赖注入
			'handler' => function(\think\App $app){
				echo 'ThinkPHP v' . $app->version() . PHP_EOL;
			}
		],
		[
			// 类型为分组任务
			'type' => 'group',
			// 分组名称
			'name' => 'group_name',
			// 子任务列表
			'tasks' => [
				[
					// 任务名称
					'name' => 'group_task_1',
					// 执行周期，单位秒
					'interval' => 3,
					// 执行器，支持闭包、类的动态方法、类的静态方法，支持参数依赖注入
					'handler' => function(){
						echo 'group_task_1 has been executed!' . PHP_EOL;
					}
				],
				[
					// 任务名称
					'name' => 'group_task_2',
					// 执行周期，单位秒
					'interval' => 6,
					// 执行器，支持闭包、类的动态方法、类的静态方法，支持参数依赖注入
					'handler' => function(){
						echo 'group_task_2 has been executed!' . PHP_EOL;
					}
				],
			],
		],
    ],
];
