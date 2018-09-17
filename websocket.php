<?php
###注意：fd指客户端唯一标识（每建立一个连接都有一个唯一标识，后期就通过该标识通信）

#1.创建websocket服务
$server = new swoole_websocket_server("0.0.0.0", 8081);
#2.握手成功，触发回调函数
$server->on('open', function (swoole_websocket_server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});
#3.收到消息，触发回调函数
$server->on('message', function (swoole_websocket_server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});
#3.关闭连接，触发回调函数
$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});
#4.启动websocket服务
$server->start();