<?php
//$mysqli = new mysqli('localhost', 'root', 'root', 'im');
//mysqli_set_charset($mysqli,'utf8');

include_once('cartImage.php');
$image = new ImgLib();

$name = trim(htmlspecialchars($_POST['name']));
$imgdata = $_FILES['headimg'];

$path = "./images/";
if(!is_dir($path)){
	mkdir($path,0777);
}
$img_new = date('YmdHis').rand(999,100000)."_im.jpg";
$res = move_uploaded_file($imgdata['tmp_name'],$path.$img_new);
if($res){
	$image_path = 'http://47.96.150.184/swoole/chartim/'.$path.$img_new;
	//$ip    = $_SERVER['REMOTE_ADDR'];
	//$date  = date('Y-m-d H:i:s');
	//$sql = "INSERT INTO `user`(`name`, `image`, `ip`, `date`, `state`) VALUES ('$name','$image_path','$ip','$date',1)";
	//$res = $mysqli->query($sql);
	//$image_path = $image->thumb($image_path,300,300);
	if($res){
		echo json_encode(array(
			'code'=>1,
			'url'=>$image_path,
			'name'=>$name
		));
	}else{
		echo json_encode(array(
			'code'=>0,
			'msg'=>'插入错误'
		));
	}
	die;
}else{
	echo json_encode(array(
		'code'=>0,
		'msg'=>'headimg'
	));
	die;
}
