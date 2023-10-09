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
    // 任务列表
    'tasks' => [
        [
			'name' => 'test',
			'interval' => 3,
			'handler' => function(\think\App $app){
				var_dump($app->version());
			}
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
		'count' => 1,
		// 支持workerman的其它配置参数
    ],
];
