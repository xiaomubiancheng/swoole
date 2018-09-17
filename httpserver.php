<?php 

#1.创建对象
$httpServer=new swoole_http_server('0.0.0.0',8081);
#2.监听端口请求：无-则不操作，有-则交给回调函�?
$httpServer->on('request',function($request,$response){
//          var_dump($request);
//          echo "<hr>";
         //var_dump($response);
	 //响应内容
         $response->end('help');
 });
#3.启动服务?
$httpServer->start();
