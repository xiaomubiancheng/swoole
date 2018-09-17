<?php
###注意：fd指客户端唯一标识（每建立一个连接都有一个唯一标识，后期就通过该标识通信）

#准备：因为是公共聊天，所以要给所有人发送数据
# 定义clientFds数组，保存所有websocket连接
$clientFds = [];

#1.创建websocket服务
$server = new swoole_websocket_server("0.0.0.0", 8080);
#2.握手成功，触发回调函数
$server->on('open', function (swoole_websocket_server $server, $request) use(&$clientFds) {
    #echo "server: handshake success with fd{$request->fd}\n";
    #将所有客户端连接标识，握手成功后保存到数组中
    $clientFds[] = $request->fd;
    #print_r($clientFds);
});
#3.收到消息，触发回调函数
$server->on('message', function (swoole_websocket_server $server, $frame) use(&$clientFds) {
    #echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    #$server->push($frame->fd, "this is server");
    #当有用户发送信息，广播通知所有用户
    foreach ($clientFds as $fd) {
        $server->push($fd, $frame->data);
    }
});
#3.关闭连接，触发回调函数
$server->on('close', function ($ser, $fd) use(&$clientFds) {
    # echo "client {$fd} closed\n";
    #注意注意，当用户关闭浏览器需要销毁客户端标识
    unset($clientFds[$fd]);
});
#4.启动websocket服务
$server->start();