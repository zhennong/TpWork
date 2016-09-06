<?php
header("Content-type: text/html; charset=gb2312"); 

include "Snoopy.class.php";

 //设置浏览器关闭也运行
ignore_user_abort();
 //设置超时时间
set_time_limit(60 * 15);

$content = file_get_contents("http://www.191.cn/hack.php?H_name=registration&c=p_dengji");

//替换函数
function replace($str){
	$str = str_replace("href=\"","href=\"http://www.191.cn/",$str);
	$str = str_replace("src=\"","src=\"http://www.191.cn/",$str);
	$str = str_replace("action=\"/hack.php?H_name=registration\"","action=\"/Snoopy/api3.php?H_name=registration\"",$str);
	$str = str_replace("href=\"http://www.191.cn//hack.php?H_name=registration&c=p_dengji\"","href=\"javascript:;\"",$str);
	$str = str_replace("data-icon=\"grid\"","style=\"display:none;\"",$str);
	
	return $str;
}
echo replace($content);

























