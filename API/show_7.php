<?php
header("Content-type: text/html; charset=utf-8");

include "Snoopy.class.php";

session_start();

 //设置超时时间
set_time_limit(60 * 15);

function submitForm(){
    $id = !empty($_GET['id']) ? $_GET['id'] : '';

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,"http://zzys.agri.gov.cn/feiliao_search.aspx?id=$id");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_HEADER,0);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

//替换函数
function replace($str){
    $str = str_replace("href=\"","href=\"http://202.127.42.157/",$str);
    $str = str_replace("src=\"","src=\"http://202.127.42.157/",$str);
    $str = str_replace("background=\"","background=\"http://202.127.42.157/",$str);
    $str = str_replace("</head>","<style>body{background: #fff;padding-top: 53px;}.Noprint{display: none;}.fixed{width:100%;line-height:40px;position: fixed;left:0;top:0;background: #0d8c1f;z-index: 9999;box-shadow: 3px 3px 3px #ccc;}.fixed a{line-height: 40px;font-size:14px;margin-left: 10px;color:#fff;}#tsearch{border:0px;padding:0px;margin:0px;border-left:1px solid #ccc;border-top:1px solid #ccc;}#tsearch td{padding:3px 5px;font-size: 14px;border-right:1px solid #ccc;border-bottom:1px solid #ccc;line-height: 25px;}#tsearch td span{margin-left: 5px;}</style></head>",$str);
    $str = str_replace("#9b0b0b","#fff",$str);
    $str = str_replace("<title>","<meta name='viewport' content='width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0'><title>",$str);
    $str = str_replace("</body>","<div class='fixed'><a href='javascript:;' onclick='history.go(-1)'>返回上一页  >></a></div></body>",$str);
    $str = str_replace("1170","100%",$str);
    $str = str_replace("985","100%",$str);
    $str = str_replace("622px","100%",$str);
    $str = str_replace("90%","100%",$str);
    $str = str_replace("148","110",$str);
    $str = str_replace("&nbsp;","",$str);
    $str = str_replace("<table width=\"100%\" style=\"height: 51px\">","<table width=\"93%\" style=\"height: 51px;margin:0 auto;\">",$str);
    $str = str_replace("cellpadding=\"1\"","cellpadding='0'",$str);
    $str = str_replace("cellspacing=\"1\"","cellspacing='0'",$str);

	return $str;
}

echo replace(submitForm());

























