<?php
use Workerman\Worker;
//use Workerman\WebServer;
//use Workerman\Lib\Timer;
use PHPSocketIO\SocketIO;

include __DIR__ . '/vendor/autoload.php';

// 全局数组保存uid在线数据
$uidConnectionMap = array();
// 记录最后一次广播的在线用户数
$last_online_count = 0;
// 记录最后一次广播的在线页面数
$last_online_page_count = 0;

/**
 * @var \libs\logger\Mono $monolog
 */
$monolog = \libs\common\Container::get('monolog');
$monolog->info('[connection]');


// PHPSocketIO服务
$context = array(
    'ssl' => array(
        'local_cert'  => '/root/key/srv_130.crt', // 也可以是crt文件
        'local_pk'    => '/root/key/srv_130.key.unsecure',
        'verify_peer' => false,
    )
);
$sender_io = new SocketIO(2120, $context);
// 客户端发起连接事件时，设置连接socket的各种事件回调
$sender_io->on('connection', function($socket){
    // 当客户端发来登录事件时触发
    $socket->on('login', function ($groups)use($socket){
        global $uidConnectionMap, $last_online_count, $last_online_page_count, $monolog;

        $monolog->info('[on][login] groups:', $groups);

        // 已经登录过了
        if(isset($socket->uid)){
            return;
        }
        // 更新对应uid的在线数据
        $uid = is_array($groups) ? (string)$groups[0] : (string)$groups;
        if(!isset($uidConnectionMap[$uid]))
        {
            $uidConnectionMap[$uid] = 0;
        }
        // 这个uid有++$uidConnectionMap[$uid]个socket连接
        ++$uidConnectionMap[$uid];
        // 将这个连接加入到uid分组，方便针对uid推送数据
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $socket->join($group);
            }
        } else {
            $socket->join($groups);
        }
        $socket->uid = $uid;

        $monolog->info('[on][login] rooms:', array_keys($socket->rooms));
        // 更新这个socket对应页面的在线数据
        $socket->emit('update_online_count', "当前<b>{$last_online_count}</b>人在线，共打开<b>{$last_online_page_count}</b>个页面");
    });
    $socket->on('viewer_login',function($groups) use ($socket){
        global $monolog;

        $monolog->info('[on][viewer_login] groups:', $groups);

        if(isset($socket->uid)){
            return;
        }
        $uid = is_array($groups) ? (string)$groups[0] : (string)$groups;
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $socket->join($group);
            }
        } else {
            $socket->join($groups);
        }
        $socket->uid = $uid;
        $game_info = explode('_', $uid);

        if($game_info){
            $game_id = $game_info[0];
            $stat    = $game_info[2];
            exec("nohup /usr/bin/php /home/wwwroot/nrt/public/admin.php viewer {$stat} {$game_id} > /dev/null 2>&1 &");
        }

    });
    // 当客户端断开连接是触发（一般是关闭网页或者跳转刷新导致）
    $socket->on('disconnect', function () use($socket) {
        global $monolog;

        $monolog->info('[on][disconnect] disconnect');

        if(!isset($socket->uid))
        {
            return;
        }
        unset($socket->uid);
        /*  global $uidConnectionMap, $sender_io;
         // 将uid的在线socket数减一
         if(--$uidConnectionMap[$socket->uid] <= 0)
         {
             unset($uidConnectionMap[$socket->uid]);
         } */
    });

    /**
     * 登出，注销所有房间。
     */
    $socket->on('logout', function () use ($socket) {
        /**
         * @var PHPSocketIO\Socket $socket
         */
        global $uidConnectionMap, $last_online_count, $last_online_page_count, $monolog;
        $monolog->info('[on][logout] groups:');

        $socket->leaveAll();
        if (isset($uidConnectionMap[$socket->uid])) {
            $uidConnectionMap[$socket->uid] = 0;
        }
        unset($socket->uid);

        $monolog->info('[on][logout] rooms:', array_keys($socket->rooms));

        $socket->emit('update_online_count', "当前<b>{$last_online_count}</b>人在线，共打开<b>{$last_online_page_count}</b>个页面");

    });

    /**
     * 加入指定房间。
     */
    $socket->on('join', function ($groups) use ($socket) {
        /**
         * @var PHPSocketIO\Socket $socket
         */
        global $uidConnectionMap, $last_online_count, $last_online_page_count, $monolog;

        $groups = is_array($groups) ? $groups : [$groups];

        $monolog->info('[on][join] groups:', $groups);

        // 更新对应uid的在线数据
        $uid = (string)$groups[0];
        if(!isset($uidConnectionMap[$uid]))
        {
            $uidConnectionMap[$uid] = 0;
        }
        ++$uidConnectionMap[$uid];

        foreach ($groups as $group) {
            if (!isset($socket->rooms[$group])) {
                $socket->join($group);
            }
        }

        $socket->uid = $uid;

        $monolog->info('[on][join] rooms:', array_keys($socket->rooms));
        // 更新这个socket对应页面的在线数据
        $socket->emit('update_online_count', "当前<b>{$last_online_count}</b>人在线，共打开<b>{$last_online_page_count}</b>个页面");
    });

    /**
     * 离开指定房间
     */
    $socket->on('leave', function ($groups) use ($socket) {
        /**
         * @var PHPSocketIO\Socket $socket
         */
        global $uidConnectionMap, $last_online_count, $last_online_page_count, $monolog;
        $groups = is_array($groups) ? $groups : [$groups];

        $monolog->info('[on][leave] groups:', $groups);

        // 更新对应uid的在线数据
        $uid = (string)$groups[0];
        if (isset($uidConnectionMap[$uid])) {
            --$uidConnectionMap[$uid];
        }

        foreach ($groups as $group) {
            if (isset($socket->rooms[$group])) {
                $socket->leave($group);
            }
        }
        if (empty($socket->rooms)) {
            unset($socket->uid);
        }

        $monolog->info('[on][leave] rooms:', array_keys($socket->rooms));

        $socket->emit('update_online_count', "当前<b>{$last_online_count}</b>人在线，共打开<b>{$last_online_page_count}</b>个页面");
    });
});

// 当$sender_io启动后监听一个http端口，通过这个端口可以给任意uid或者所有uid推送数据
$sender_io->on('workerStart', function(){
    global $monolog,$context;

    $monolog->info('[on][workerStart]...');

    // 监听一个http端口
    /*$context = array(
        'ssl' => array(
            'local_cert'  => '/etc/nginx/conf.d/ssl/server.pem', // 也可以是crt文件
            'local_pk'    => '/etc/nginx/conf.d/ssl/server.key',
            'verify_peer' => false,
        )
    );*/
    $inner_http_worker = new Worker('http://0.0.0.0:2121', $context);
    $inner_http_worker->transport = 'ssl';
    // 当http客户端发来数据时触发
    $inner_http_worker->onMessage = function($http_connection, $data){
        global $monolog;
        $_POST = $_POST ? $_POST : $_GET;
        // 推送数据的url格式 type=publish&to=uid&content=xxxx
        switch(@$_POST['type']){
            case 'publish':
                global $sender_io;
                $to = @$_POST['to'];

                if(isset($_POST['content'])){
                    $_POST['content'] = json_decode($_POST['content'],true);
                }else{
                    $_POST['content'] = '';
                }

                // 有指定uid则向uid所在socket组发送数据
                if($to){
                    $sender_io->to($to)->emit('new_msg', $_POST['content']);
                    // 否则向所有uid推送数据
                }else{
                    $sender_io->emit('new_msg', @$_POST['content']);
                }

                $monolog->info('[on][message][publish] to: ');
                $monolog->info($to);
                $monolog->info('[on][message][publish] content: ', @$_POST['content']);

                break;
            case 'gamePush':
                global $sender_io;
                $to = @$_POST['to'];
                if(isset($_POST['content'])){
                    $_POST['content'] = json_decode($_POST['content'],true);
                }else{
                    $_POST['content'] = '';
                }

                if($to){
                    $sender_io->to($to)->emit('gamePush', $_POST['content']);
                }else{// 否则向所有uid推送数据
                    $sender_io->emit('gamePush', @$_POST['content']);
                }

                $monolog->info('[on][message][gamePush] to: ' . $to, @$_POST['content']);

                break;
            // http接口返回ok
            case 'loginTimeout':
                global $sender_io;
                $to = @$_POST['to'];
                if(isset($_POST['content'])){
                    $_POST['content'] = json_decode($_POST['content'],true);
                }else{
                    $_POST['content'] = '';
                }

                // 有指定uid则向uid所在socket组发送数据
                if($to){
                    $sender_io->to($to)->emit('loginTimeout', $_POST['content']);
                    // 否则向所有uid推送数据
                }else{
                    $sender_io->emit('loginTimeout', @$_POST['content']);
                }

                $monolog->info('[on][message][loginTimeout] to: ');
                $monolog->info($to);
                $monolog->info('[on][message][loginTimeout] content: ', @$_POST['content']);

                break;
        }
        return $http_connection->send('ok');
    };
    // 执行监听
    $inner_http_worker->listen();

    // 一个定时器，定时向所有uid推送当前uid在线数及在线页面数
    /* Timer::add(1, function(){
        global $uidConnectionMap, $sender_io, $last_online_count, $last_online_page_count;
        $online_count_now = count($uidConnectionMap);
        $online_page_count_now = array_sum($uidConnectionMap);
        // 只有在客户端在线数变化了才广播，减少不必要的客户端通讯
        if($last_online_count != $online_count_now || $last_online_page_count != $online_page_count_now)
        {
            $sender_io->emit('update_online_count', "当前<b>{$online_count_now}</b>人在线，共打开<b>{$online_page_count_now}</b>个页面");
            $last_online_count = $online_count_now;
            $last_online_page_count = $online_page_count_now;
        }
    }); */
});

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
