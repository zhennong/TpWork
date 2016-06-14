<?php
header("Content-type: text/html; charset=gb2312"); 

include "Snoopy.class.php";

session_start();

 //设置超时时间
set_time_limit(60 * 15);

if(empty($_SESSION['a6_c'])){
	$_SESSION['a6_c']=$_POST['c'];
}

if(empty($_SESSION['a6_a'])){
	$_SESSION['a6_a']=$_POST['a'];
}

if(empty($_SESSION['a6_key'])){
	$_SESSION['a6_key']= mb_convert_encoding($_POST['key'],"gb2312","utf-8");
}else{
	if($_SESSION['a6_key'] != mb_convert_encoding($_POST['key'],"gb2312","utf-8")){
		$_SESSION['a6_key']= mb_convert_encoding($_POST['key'],"gb2312","utf-8");
	}
}

function submitForm(){	
	$snoopy = new Snoopy;	
	
	$formvars["c"] = $_SESSION['a6_c'];
	$formvars["a"] = $_SESSION['a6_a'];
	$formvars["key"] = $_SESSION['a6_key'];
		
	$action = "http://www.191.cn/hack.php?H_name=registration";

	$snoopy->agent = "(compatible; MSIE 4.01; MSN 2.5; AOL 4.0; Windows 2000)"; //伪装浏览器
	$snoopy->referer = "http://www.191.cn"; //伪装来源页地址 http_referer
	$snoopy->rawheaders["Pragma"] = "no-cache"; //cache 的http头信息
	$snoopy->rawheaders["X_FORWARDED_FOR"] = "127.0.0.1"; //伪装ip
	
	$snoopy->submit($action,$formvars);
	$data = $snoopy->results;	
				
	return $data;	
}

//替换函数
function replace($str){
	$str = str_replace("href=\"","href=\"http://www.191.cn/",$str);
	$str = str_replace("src=\"","src=\"http://www.191.cn/",$str);
	
	$str = str_replace("href=\"http://www.191.cn/javascript:;","href=\"javascript:;",$str);
	
	$str = str_replace("href=\"http://www.191.cn//hack.php","href=\"/Snoopy/show3.php",$str);
	$str = str_replace("data-icon=\"grid\"","style=\"display:none;\"",$str);
	$str = str_replace("href=\"/Snoopy/show3.php?H_name=registration&c=f_nongyebu\"","href=\"javascript:;\" onclick=\"history.go(-1)\"",$str);
	
	return $str;
}

echo replace(submitForm());

























