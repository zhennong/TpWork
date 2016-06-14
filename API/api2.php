<?php
header("Content-type: text/html; charset=gb2312"); 

include "Snoopy.class.php";

session_start();

 //设置超时时间
set_time_limit(60 * 15);

if(empty($_SESSION['a2_c'])){
	$_SESSION['a2_c']=$_POST['c'];
}

if(empty($_SESSION['a2_a'])){
	$_SESSION['a2_a']=$_POST['a'];
}

if(empty($_SESSION['a2_radio-choice-h-1'])){
	$_SESSION['a2_radio-choice-h-1'] = mb_convert_encoding($_POST['radio-choice-h-1'],"gb2312","utf-8");
}else{
	if($_SESSION['a2_radio-choice-h-1'] != mb_convert_encoding($_POST['radio-choice-h-1'],"gb2312","utf-8")){
		$_SESSION['a2_radio-choice-h-1']= mb_convert_encoding($_POST['radio-choice-h-1'],"gb2312","utf-8");
	}
}

if(empty($_SESSION['a2_key'])){
	$_SESSION['a2_key'] = mb_convert_encoding($_POST['key'],"gb2312","utf-8");
}else{
	$_SESSION['a2_key'] = mb_convert_encoding($_POST['key'],"gb2312","utf-8");
}

function submitForm(){	
	$snoopy = new Snoopy;	
	
	$formvars["c"] = $_SESSION['a2_c'];
	$formvars["a"] = $_SESSION['a2_a'];
	$formvars["radio-choice-h-1"] = $_SESSION['a2_radio-choice-h-1'];
	$formvars["key"] = $_SESSION['a2_key'];
		
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
	$str = str_replace("href=\"http://www.191.cn//hack.php?H_name=registration&c=f_nongyebu\"","href=\"javascript:;\" onclick=\"history.go(-1)\"",$str);
	$str = str_replace("data-icon=\"grid\"","style=\"display:none;\"",$str);
	$str = str_replace("</head>","<style>.ui-content{padding:6px;}.query td{padding:8px 3px;}</style></head>",$str);
	
	$str = str_replace("</head>","<script type='text/javascript' href='http://apps.bdimg.com/libs/jquery/2.1.1/jquery.min.js'></script><script type='text/javascript'>	
	$(function(){
		$(\"table tr td:eq(0)\").attr('width','13%');
		$(\"table tr td:eq(1)\").attr('width','20%');
		$(\"table tr td:eq(2)\").attr('width','23%');
		$(\"table tr td:eq(3)\").attr('width','26%');
		$(\"table tr td:eq(4)\").attr('width','13%');
	})	
	</script></head>",$str);
	
	return $str;
}

echo replace(submitForm());

























