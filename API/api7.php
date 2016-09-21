<?php
header("Content-type: text/html; charset=utf-8");

include "Snoopy.class.php";

session_start();

 //设置超时时间
set_time_limit(60 * 15);

function submitForm(){	
	$snoopy = new Snoopy;

    $formvars["__VIEWSTATE"] = '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';
    $formvars["__VIEWSTATEGENERATOR"] = '6BF14D9B';
    $formvars["__EVENTVALIDATION"] = '/wEWNAKy8sn6AQL1mfYJAqOY1K8GAoWhmKsDAs3dh78FAp+x74IMAo/tn8cCArDXvsQPAunyl70KAsHbwPwNApa5n+YLAsSGga4HAtGd5ogJAv3WyuIOAvuHltUOAvvOrZIPArW1+fwKAvrflnQCz+228wIC5KHONwL4y7rxDALHlYUIAv+Ske0MAo2hsL8LAourloQOAr6BhggCkaqEwwkCyvTYvAoCyOqI5AkC3LXFeQK53IrXCwKdleufBwLwuN/NBAKdraC2DwL7zunkDgLX146OBAL7ztXjDgLS3PrvBwL+lYGlCwLtxZiFAgLynvfvCAKcir/FAwKA+8LcCwK389zaDQKOhdmoAgKz7tXMCQKx7anUCAL7tqzyCgL7ttzcCAKUhOreBgKazKnUCAKln/OLAnrD9aaXUKlpOtoT98j127zLmaeW64wC4L/GgW8qV9oD';
    $formvars["enterprise_name"] = !empty($_GET["enterprise_name"]) ? $_GET['enterprise_name'] : '';   //公司名称
    $formvars["Select3"] = 'and';
    $formvars["Sproduct"] = !empty($_GET["Sproduct"]) ? $_GET['Sproduct'] : '所有类型';
    $formvars["Select4"] = 'and';
    $formvars["shangbiao"] = '';
    $formvars["Select5"] = 'and';
    $formvars["txtzuowu"] = '';
    $formvars["Select6"] = 'and';
    $formvars["txtzh"] = !empty($_GET["txtzh"]) ? $_GET['txtzh'] : '';            //登记证号
    $formvars["Select7"] = 'and';
    $formvars["Status"] = '所有类型';
    $formvars["btnsearch"] = '搜索';
    $formvars["page"] = !empty($_GET["page"]) ? $_GET['page'] : '';
    $formvars["e"] = !empty($_GET["e"]) ? $_GET['e'] : '';
    $formvars["h"] = !empty($_GET["h"]) ? $_GET['h'] : '';
		
	$action = "http://202.127.42.157/moazzys/feiliao.aspx";

	$snoopy->agent = "(compatible; MSIE 4.01; MSN 2.5; AOL 4.0; Windows 2000)"; //伪装浏览器
	$snoopy->referer = "http://202.127.42.157"; //伪装来源页地址 http_referer
	$snoopy->rawheaders["Pragma"] = "no-cache"; //cache 的http头信息
	$snoopy->rawheaders["X_FORWARDED_FOR"] = "127.0.0.1"; //伪装ip
	
	$snoopy->submit($action,$formvars);
	$data = $snoopy->results;	
				
	return $data;	
}

//替换函数
function replace($str){
    $str = str_replace("href=\"","href=\"http://202.127.42.157/",$str);
    $str = str_replace("src=\"","src=\"http://202.127.42.157/",$str);
    $str = str_replace("background=\"","background=\"http://202.127.42.157/",$str);
    $str = str_replace("</head>","<style>body{background: #fff;padding-top:20px;}.Noprint{display: none;}#Gvfl{margin:0 auto;}#Gvfl th{padding:6px 5px;}#Gvfl td{padding: 3px 5px;border-bottom:1px solid #999;}#Gvfl td a{color:red;}#tsearch{display: none;}.pager{padding-bottom: 20px;font-size: 14px;}.pager a{margin:0 3px;font-size: 14px;}.pager a.current{color:red;}.fixed{width:100%;line-height:40px;position: fixed;left:0;top:0;background: #0d8c1f;z-index: 9999;box-shadow: 3px 3px 3px #ccc;}.fixed a{line-height: 40px;font-size:14px;margin-left: 10px;color:#fff;}#txtcount{display: none;}</style></head>",$str);
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
    $str = str_replace("#FF9933","#53b97f",$str);
    $str = str_replace("id=\"Gvfl\" width=\"100%\"","id=\"Gvfl\" width=\"93%\"",$str);
    $str = str_replace("feiliao_search.aspx","show_7.php",$str);
	return $str;
}

echo replace(submitForm());

























