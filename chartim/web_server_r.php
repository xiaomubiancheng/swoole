<?php
$ws = new swoole_websocket_server('47.96.150.184', 8080);
//配置
$ws->set(array(
		'task_worker_num' => 8,
		'worker_num' => 4,
		'daemonize'=>1
	)
);

$redis = new Redis;
$redis->connect('127.0.0.1', 6379);

//$clients = array();//client连接池,操蛋，task独立进程，对global $clients 局部限制了，得用缓存

//启动触发
$ws->on('start',function() use ($redis) {
	echo "服务启动\r\n";
	$redis->del('clients');//启动初始化
});

//客户端连接成功后触发
$ws->on('open', function($ws, $request){
	echo "Someone have connected!\r\n";
	//$ws->push($request->fd, 'hello world!');
});

//task异步任务处理
$ws->on('task',function($ws, $task_id, $from_id, $data){
    handleData($ws,$data);
	$ws->finish('ok');
});
//task处理结束回调
$ws->on('finish',function($ws, $task_id, $data){
	echo $data."\r\n";
});

//接收请求回调
$ws->on('message', function($ws, $frame){
    parse_str($frame->data, $data);
	$data['id'] = $frame->fd;
	$task_id = $ws->task($data);//投递task异步任务
});

//关闭连接
$ws->on('close', function($ws, $fd){
	global $redis;
	$clients = json_decode($redis->get('clients'), true);
    $data = array(
		'flag'=>'leave',
		'id'=>$fd,
		'name'=>$clients[$fd]['name']
	);
	if(isset($clients[$fd])){
		unset($clients[$fd]);
		sendmsg($ws,$clients, $data);
		$redis->set('clients', json_encode($clients));
		echo "client:{$fd} has closed\r\n";
	}
});

//启动
$ws->start();

//处理消息
function handleData($ws, $data){
	global $redis;
	$clients = json_decode($redis->get('clients'), true);
	$old_clients = $clients;

    if($data['flag'] == 'new'){
		$clients[$data['id']] = array(
			'name'=>$data['name'],
			'img'=>$data['img']
		);
		$redis->set('clients', json_encode($clients));
		$data['clients'] = $clients;
		$ws->push($data['id'], json_encode($data));//新人进来单独发送一份消息
		unset($data['clients']);
	}

	//接收图片消息
	if($data['flag'] == 'pic'){
		//图片二进制流内的'+'会被转为空格，整理处理一下
		$data['msg'] = preg_replace('/ /', '+', $data['msg']);
	}

	//@召唤
	if(isset($data['msg'])){
		preg_match_all('/data-to="(.*?)"/', $data['msg'], $arr_to);
		if(!empty($arr_to[1])){
			$data['at'] = $arr_to[1];
		}
	}

	$data['img'] = $clients[$data['id']]['img'];

	//连续五分钟内交互，显示一次时间
	if(!$redis->get('web_socket_time')){
		$redis->set('web_socket_time', 1, 300);
		$data['date'] = date('Y-m-d H:i:s');
	}
	$redis->set('web_socket_time', 1, 300);

	//私聊
	if(isset($data['private'])){
		$sl_arr[$data['id']] = $data['id'];
		$sl_arr[$data['for_id']] = $data['for_id'];
		sendmsg($ws,$sl_arr,$data);
	}else{
		if($old_clients){
			sendmsg($ws,$old_clients,$data);
		}
	}
}

//发送消息
function sendmsg($ws,$clients,$data){
	foreach($clients as $fd => $name){
        $ws->push($fd, json_encode($data));
	}
}
