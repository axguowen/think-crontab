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
	 * @param App $app 应用实例
     * @param Worker $worker
     * @param Input $input 输入
     * @param Output $output 输出
     * @return void
     */
    public static function onWorkerStart(App $app, Worker $worker, Input $input, Output $output)
    {
        // 获取配置中的任务列表
        $task_list = $app->config->get('crontab.task_list');
        // 如果任务列表为空
        if(empty($task_list)){
            // 输出错误并返回
            return $output->writeln('<error>The task list is empty</error>');
        }

        // 获取当前任务
        $task = $task_list[$worker->id];
        // 如果是普通任务
        if(!isset($task['type']) || $task['type'] != 'group'){
            // 合并配置
            $task = array_merge([
                'name' => 'unnamed',
                'interval' => 60,
            ], $task);
            // 如果执行器为空
            if(!isset($task['handler']) || empty($task['handler'])){
                // 输出错误并返回
                return $output->writeln('<error>The task: ' . $task['name'] . ' in process id: ' . $worker->id . ' is start failed, reason: task handler is empty</error>');
            }
            // 获取执行器
            $handler = $task['handler'];
            // 如果是类名则指定方法
            if (is_string($handler) && false === strpos($handler, '::')) {
                $handler = [$handler, 'handle'];
            }
            // 加入到定时器并返回
            return Timer::add($task['interval'], function ($callable, $app){
                // 如果是闭包
                if ($callable instanceof \Closure) {
                    // 执行闭包
                    return $app->invokeFunction($callable);
                }
                // 执行类的方法
                return $app->invokeMethod($callable);
            }, [$handler, $app]);
        }

        // 如果是任务组
        // 合并配置
        $group = array_merge(['name' => 'unnamed'], $task);
        // 如果是任务组但未设置任务列表或者为空
        if(!isset($group['tasks']) || !is_array($group['tasks']) || empty($group['tasks'])){
            // 输出错误并返回
            return $output->writeln('<error>The group: ' . $group['name'] . ' in process id: ' . $worker->id . ' is start failed, reason: group task is empty</error>');
        }
        // 如果是任务组则遍历任务列表
        foreach($group['tasks'] as $task){
            // 合并配置
            $task = array_merge([
                'name' => 'unnamed',
                'interval' => 60,
            ], $task);

            // 如果执行器为空
            if(!isset($task['handler']) || empty($task['handler'])){
                // 输出错误并返回
                $output->writeln('<error>The task: ' . $task['name'] . ' in process id: ' . $worker->id . ' is start failed, reason: task handler is empty</error>');
                continue;
            }
            $handler = $task['handler'];
            // 如果是类名则指定方法
            if (is_string($handler) && false === strpos($handler, '::')) {
                $handler = [$handler, 'handle'];
            }
            // 加入到定时器并返回
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
    }
}
