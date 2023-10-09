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

class Events
{
    /**
     * onWorkerStart 事件回调
     * @access public
     * @param Worker $worker
     * @param Worker $worker
     * @return void
     */
    public static function onWorkerStart(App $app, Worker $worker, Input $input, Output $output)
    {
        // 获取配置中的任务
        $tasks = $app->config->get('crontab.tasks');
        // 如果任务为空
        if(empty($tasks)){
            $output->writeln('<error>The task list is empty</error>');
            return;
        }
        // 默认参数
        $config = [
            // 任务标识
            'name' => '',
            // 任务间隔时间
            'interval' => 60,
            // 执行器
            'handler' => '',
        ];
        // 任务索引
        $index = 0;
        // 启动失败的任务列表
        $faileds = [];
        // 等待时间
        sleep($worker->id);
        // 遍历任务列表
        foreach($tasks as $task){
            $index++;
            // 任务名称
            $name = 'The task [' . $task['name'] . ']';
            if(empty($task['name'])){
                $name = 'The task index [' . $index . ']';
            }
            // 如果未指定
            $task = array_merge($config, $task);
            if(empty($task['handler'])){
                // 任务名称
                $faileds[] = [
                    'name' => $name,
                    'reason' => 'handler is empty',
                ];
                continue;
            }
            $handler = $task['handler'];
            // 如果是类名则指定方法
            if (is_string($handler) && false === strpos($handler, '::')) {
                $handler = [$handler, 'handler'];
            }
            // 加入到定时器
            Timer::add($task['interval'], function ($callable, $app){
                // 如果是闭包
                if ($callable instanceof \Closure) {
                    // 执行闭包
                    return $app->invokeFunction($callable);
                }
                // 执行类的方法
                return $app->invokeMethod($callable);
            }, [$handler, $app]);
        }
        
        // 如果失败的不为空
        if(!empty($faileds)){
            foreach($faileds as $failed){
                $output->writeln('<error>' . $failed['name'] . ' is start failed, reason: ' . $failed['reason'] . '</error>');
            }
        }
    }
}
