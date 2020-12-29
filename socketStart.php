<?php
use Workerman\Worker;
include __DIR__ . '/vendor/autoload.php';
 
// 创建一个Worker监听2346端口，使用socket协议通讯
$tcp_worker = new Worker('tcp://0.0.0.0:2348');
 
// 启动4个进程对外提供服务
$tcp_worker->count = 8;
 
// 当收到客户端发来的数据后返回hello $data给客户端
$tcp_worker->onMessage = function($connection, $data)
{
	// exec("nohup /usr/bin/php /home/wwwroot/nrt/public/admin.php viewer socket-push '{$data}' >> /var/log/socket.info 2>&1 &");
	exec("nohup /usr/bin/php /www/SimpleCRC/yii defence/socket-push '{$data}' >> /var/log/socket.info 2>&1 &");

    // 向客户端发送hello $data
   // file_put_contents('./socket.info', $data);
    $connection->send('send ok');
};
// 运行
Worker::runAll();