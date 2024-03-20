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

namespace think\crontab;

use think\App;
use think\console\Input;
use think\console\Output;
use Workerman\Worker;
use Workerman\Lib\Timer;

class Crontab
{
    /**
     * 配置参数
     * @var array
     */
	protected $options = [
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
        'task_list' => [],
	];

    /**
     * App实例
     * @var App
     */
    protected $app;

    /**
     * Worker实例
     * @var Worker
     */
    protected $worker;

    /**
     * Input实例
     * @var Input
     */
    protected $input;

    /**
     * Output实例
     * @var Output
     */
    protected $output;

    /**
	 * worker回调方法
	 * @var array
	 */
	protected $events = ['onWorkerStart'];

    /**
     * 架构函数
     * @access public
     * @return void
     */
    public function __construct(App $app, Input $input, Output $output)
    {
        $this->app = $app;
        $this->input = $input;
        $this->output = $output;
        // 合并配置
		$this->options = array_merge($this->options, $this->app->config->get('crontab'));
        // 实例化worker
        $this->worker = new Worker();
        // 初始化
		$this->init();
    }

    /**
     * 初始化
     * @access protected
	 * @return void
     */
	protected function init()
	{
        // 获取实例名称
        $this->worker->name = $this->options['name'];
        if(empty($this->worker->name)){
            $this->worker->name = 'think-crontab';
        }

        // 设置进程数
        $this->worker->count = count($this->options['task_list']);

		// 内容输出文件路径
		if(!empty($this->options['stdout_file'])){
			// 目录不存在则自动创建
			$stdout_dir = dirname($this->options['stdout_file']);
			if (!is_dir($stdout_dir)){
				mkdir($stdout_dir);
			}
			// 指定stdout文件路径
			Worker::$stdoutFile = $this->options['stdout_file'];
		}

		// pid文件路径
		if(empty($this->options['pid_file'])){
			$this->options['pid_file'] = $this->app->getRuntimePath() . 'worker' . DIRECTORY_SEPARATOR . $this->worker->name . '.pid';
		}
		// 目录不存在则自动创建
		$pid_dir = dirname($this->options['pid_file']);
		if (!is_dir($pid_dir)){
			mkdir($pid_dir);
		}
		// 指定pid文件路径
		Worker::$pidFile = $this->options['pid_file'];
		
		// 日志文件路径
		if(empty($this->options['log_file'])){
			$this->options['log_file'] = $this->app->getRuntimePath() . 'worker' . DIRECTORY_SEPARATOR . $this->worker->name . '.log';
		}
		// 目录不存在则自动创建
		$log_dir = dirname($this->options['log_file']);
		if (!is_dir($log_dir)){
			mkdir($log_dir);
		}
		// 指定日志文件路径
		Worker::$logFile = $this->options['log_file'];

        // 如果指定以守护进程方式运行
        if ($this->input->hasOption('daemon') || true === $this->options['daemonize']) {
            Worker::$daemonize = true;
        }
	}

    /**
     * 启动
     * @access public
	 * @return void
     */
	public function start()
	{
		// 设置回调
        foreach ($this->events as $event) {
            if (method_exists($this, $event)) {
                $this->worker->$event = [$this, $event];
            }
        }

        // 启动
		Worker::runAll();
	}

    /**
     * 启动回调
     * @access public
	 * @param Worker $worker
	 * @return void
     */
    public function onWorkerStart(Worker $worker)
    {
        // 清除opcache缓存
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        // 启动
        $this->app->invokeMethod(Events::class . '::onWorkerStart', [$worker, $this->input, $this->output]);
    }

    /**
     * 停止
     * @access public
     * @return void
     */
    public function stop()
    {
        Worker::stopAll();
    }

    public function __set($name, $value)
    {
        $this->worker->$name = $value;
    }

    public function __call($method, $args)
    {
        call_user_func_array([$this->worker, $method], $args);
    }
}
