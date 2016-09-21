<?php
header("Content-type: text/html; charset=utf-8");

include "Snoopy.class.php";

session_start();

 //设置超时时间
set_time_limit(60 * 15);

function submitForm(){
    $page = !empty($_GET['page']) ? $_GET['page'] : '';
    $e = !empty($_GET['e']) ? $_GET['e'] : '';
    $h = !empty($_GET['h']) ? $_GET['h'] : '';

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,"http://zzys.agri.gov.cn/feiliao.aspx?page=$page&e=$e&t=&p=&z=&h=$h&x=");
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
    $str = str_replace("</head>","<style>body{background: #fff;padding-top:20px;}.Noprint{display: none;}#Gvfl{margin:0 auto;}#Gvfl th{padding:6px 5px;}#Gvfl td{padding: 3px 5px;border-bottom:1px solid #999;}#Gvfl td a{color:red;}#tsearch{display: none;}.pager{padding-bottom: 20px;font-size: 14px;}.pager a{margin:0 3px;font-size: 14px;}.pager a.current{color:red;}.fixed{width:100%;line-height:40px;position: fixed;left:0;top:0;background: #0d8c1f;z-index: 9999;box-shadow: 3px 3px 3px #ccc;}.fixed a{line-height: 40px;font-size:14px;margin-left: 10px;color:#fff}#txtcount{display: none;}</style></head>",$str);
    $str = str_replace("#9b0b0b","#fff",$str);
    $str = str_replace("<title>","<meta name='viewport' content='width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0'><title>",$str);
    $str = str_replace("?page=","api7_2.php?page=",$str);
    $str = str_replace("</body>","<div class='fixed'><a href='javascript:;' onclick='history.go(-1)'>返回上一页 >></a></div></body>",$str);
    $str = str_replace("1170","100%",$str);
    $str = str_replace("622px","100%",$str);
    $str = str_replace("90%","100%",$str);
    $str = str_replace("50","80",$str);
    $str = str_replace("DodgerBlue","#999",$str);
    $str = str_replace("【","",$str);
    $str = str_replace("】","",$str);
    $str = str_replace("&nbsp;","",$str);
    $str = str_replace("共","",$str);
    $str = str_replace("条数据","",$str);
    $str = str_replace("分页：","",$str);
    $str = str_replace("_blank","_self",$str);
    $str = str_replace("页","&nbsp;页&nbsp;&nbsp;",$str);
    $str = str_replace("feiliao_search.aspx","show_7.php",$str);
    $str = str_replace("#FF9933","#53b97f",$str);
    $str = str_replace("id=\"Gvfl\" width=\"100%\"","id=\"Gvfl\" width=\"95%\"",$str);
    $str = str_replace("feiliao_search.aspx","show_7.php",$str);
	return $str;
}

echo replace(submitForm());

























