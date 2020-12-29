<?php
/**
 * run with command 
 * php start.php start
 */

use Workerman\Worker;
use Libs\Common\Container;
use Libs\Logger\Mono;

// composer 的 autoload 文件
include __DIR__ . '/vendor/autoload.php';

/*if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    exit("start.php not support windows, please use start_for_win.bat\n");
}*/


// 配置日志
//define('ENV', 'prod');
define('ENV', 'debug');
$log_file = __DIR__ . DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'debug.log';
$monolog = new Mono($log_file, 'start.php');
Container::set('monolog', $monolog);


// 标记是全局启动
define('GLOBAL_START', 1);

// 加载IO 和 Web
require_once __DIR__ . '/start_io.php';
//require_once __DIR__ . '/start_web.php';

// 运行所有服务
Worker::runAll();