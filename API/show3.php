<?php
header("Content-type: text/html; charset=gb2312"); 

include "Snoopy.class.php";

 //设置浏览器关闭也运行
ignore_user_abort();
 //设置超时时间
set_time_limit(60 * 15);

function submitForm(){	
	$snoopy = new Snoopy;
	$snoopy->fetch("http://www.191.cn//hack.php?H_name=registration&c=".$_GET["c"]."&a=".$_GET["a"]."&regnum=".$_GET["regnum"]); 		//获取所有内容
	return $snoopy->results; 		//显示结果
}

//替换函数
function replace($str){
	$str = str_replace("href=\"","href=\"http://www.191.cn/",$str);
	$str = str_replace("src=\"","src=\"http://www.191.cn/",$str);
	$str = str_replace("action=\"/hack.php?H_name=registration\"","action=\"/Snoopy/api3.php?H_name=registration\"",$str);
	$str = str_replace("href=\"http://www.191.cn//hack.php?H_name=registration&c=p_dengji\"","href=\"javascript:;\" onclick=\"history.go(-1)\"",$str);
	$str = str_replace("data-icon=\"grid\"","style=\"display:none;\"",$str);
	
	return $str;
}

echo replace(submitForm());

























