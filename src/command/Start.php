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

namespace think\crontab\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\crontab\Crontab;

class Start extends Command
{
    /**
     * 配置
     * @access protected
     * @return void
     */
    protected function configure()
    {
        // 指令配置
        $this->setName('worker')
            ->addArgument('action', Argument::OPTIONAL, 'start|stop|restart|reload|status', 'start')
            ->addOption('daemon', 'd', Option::VALUE_NONE, 'Run the crontab in daemon mode.')
            ->setDescription('A Simple Crontab Extend For ThinkPHP');
    }

    /**
     * 执行命令
     * @access protected
     * @param Input $input 输入
     * @param Output $output 输出
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        // 获取参数
        $action = $input->getArgument('action');
        // 如果是linux系统
        if (DIRECTORY_SEPARATOR !== '\\') {
            if (!in_array($action, ['start', 'stop', 'reload', 'restart', 'status'])) {
                $output->writeln('<error>Invalid argument action:' . $action . ', Expected start|stop|restart|reload|status.</error>');
                return false;
            }

            global $argv;
            array_shift($argv);
            array_shift($argv);
            array_unshift($argv, 'think', $action);
        }
        // windows只支持start方法
        elseif ('start' != $action) {
            $output->writeln('<error>Not Support action:' . $action . ' on Windows.</error>');
            return false;
        }

        // 如果是启动
        if ('start' == $action) {
            $output->writeln('Starting crontab service...');
        }

        // 实例化
        $crontab = $this->app->make(Crontab::class, [$input, $output]);

        if (DIRECTORY_SEPARATOR == '\\') {
            $output->writeln('You can exit with <info>`CTRL-C`</info>');
        }

        // 启动
		$crontab->start();
    }
}