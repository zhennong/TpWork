<?php
header("Content-type: text/html; charset=utf-8"); 

include "Snoopy.class.php";

 //设置浏览器关闭也运行
ignore_user_abort();
 //设置超时时间
set_time_limit(60 * 15);

function submitForm(){	
	$snoopy = new Snoopy;
	$snoopy->fetch("http://appserver.nongyaotong.com/nyt_wx/showDtl?id=".$_GET['id']."&flag=".$_GET['flag']); 		//获取所有内容
	return $snoopy->results; 		//显示结果
}

//替换函数
function replace($str){
	$str = str_replace("href=\"/","href=\"http://appserver.nongyaotong.com/",$str);
	$str = str_replace("src=\"/","src=\"http://appserver.nongyaotong.com/",$str);
	$str = str_replace("data-role=\"footer\"","style=\"display:none;\"",$str);
	$str = str_replace("window.location.href = \"/","window.location.href = \"http://appserver.nongyaotong.com/",$str);
	$str = str_replace("action=\"/","action=\"http://appserver.nongyaotong.com/",$str);
	$str = str_replace("http://www.v-dz.com//res/html/0e58e632-b320-4f4f-9819-cd02fff0c990.html","javascript:void(0)",$str);
	$str = str_replace("http://appserver.nongyaotong.com/nyt_wx/search8","/Snoopy/index.php",$str);
	$str = str_replace("http://appserver.nongyaotong.com/nyt_wx/showBQDtl","/Snoopy/showBQDtl.php",$str);
	
	$str = str_replace("</head>","<link rel=\"stylesheet\" type=\"text/css\" href=\"css/override.css\" /></head>",$str);
	
	return $str;
}

echo replace(submitForm());

























