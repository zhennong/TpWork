<?php
header("Content-type: text/html; charset=utf-8"); 

include "Snoopy.class.php";

 //设置浏览器关闭也运行
ignore_user_abort();
 //设置超时时间
set_time_limit(60 * 15);

function submitForm(){	
	$snoopy = new Snoopy;	
	
	$formvars["footer"] = $_GET['footer'];
	$formvars["header"] = $_GET['header'];
	$formvars["submitFlag"] = $_GET['submitFlag'];
	$formvars["flag"] = $_GET['flag'];
	$formvars["pageNo"] = $_GET['pageNo'];	
	
	$formvars["certificateNo"] = $_GET['certificateNo']; //登记正好
	$formvars["productName"] = $_GET['productName']; //产品名称
	$formvars["company"] = $_GET['company']; //企业名称
	$formvars["productType"] = $_GET['productType']; //农药类别
	$formvars["agentType"] = $_GET['agentType']; //剂型
	$formvars["crop"] = $_GET['crop']; //作物
	$formvars["preventionObject"] = $_GET['preventionObject']; //防治对象	
	$formvars["composition"] = $_GET['composition']; //有效成分
	$formvars["danHunJi"] = $_GET['danHunJi']; //剂型
	$formvars["isValid"] = $_GET['isValid']; //有●无效
	$formvars["note"] = $_GET['note']; //是否出口
	
	$action = "http://appserver.nongyaotong.com/nyt_wx/search";

	$snoopy->cookies["ASPSESSIONIDQQSQRRCR"] = 'HAOFCFNACDGNABLEHBDEMLAF'; //伪装sessionid
	$snoopy->agent = "(compatible; MSIE 4.01; MSN 2.5; AOL 4.0; Windows 2000)"; //伪装浏览器
	$snoopy->referer = "http://appserver.nongyaotong.com"; //伪装来源页地址 http_referer
	$snoopy->rawheaders["Pragma"] = "no-cache"; //cache 的http头信息
	$snoopy->rawheaders["X_FORWARDED_FOR"] = "127.0.0.1"; //伪装ip
	
	$snoopy->submit($action,$formvars);
	$data = $snoopy->results;
	return $data;	
}

//替换函数
function replace($str){
	$str = str_replace("href=\"/","href=\"http://appserver.nongyaotong.com/",$str);
	$str = str_replace("src=\"/","src=\"http://appserver.nongyaotong.com/",$str);
	$str = str_replace("data-role=\"footer\"","style=\"display:none;\"",$str);
	$str = str_replace("window.location.href = \"/","window.location.href = \"http://appserver.nongyaotong.com/",$str);
	$str = str_replace("action=\"/","action=\"http://appserver.nongyaotong.com/",$str);
	$str = str_replace("http://appserver.nongyaotong.com/nyt_wx/showDtl","show.php",$str);
	$str = str_replace("http://appserver.nongyaotong.com/nyt_wx/search","api.php",$str);
	$str = str_replace("http://appserver.nongyaotong.com/nyt_wx/page_8?header=&footer=","nysz.php",$str);
	$str = str_replace("http://appserver.nongyaotong.com/nyt_wx/page_1_1?header=&footer=","javascript:history.back(-1)",$str);
	
	$str = str_replace("#e4c4c4","#f8f8f8",$str);
	$str = str_replace("#1C6AB1","#333",$str);
	
	$str = str_replace("background-color: #ffcbb3;","display:none;",$str);
	$str = str_replace("href=\"#rightpanel\"","style=\"display:none;\"",$str);
	$str = str_replace("color: red","color:#666",$str);
	
	$str = str_replace("height: 24px;","height: 25px;",$str);
	$str = str_replace("onclick=\"backPage()\"","onclick=\"backPage()\" style=\"color:#fff\"",$str);
	
	$str = str_replace("</head>","<link rel=\"stylesheet\" type=\"text/css\" href=\"css/override.css\" /></head>",$str);
	
	return $str;
}

echo replace(submitForm());

























