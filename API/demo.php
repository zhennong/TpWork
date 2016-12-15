<?php
header("Content-type: text/html; charset=utf-8");

include "Snoopy.class.php";

//设置浏览器关闭也运行
ignore_user_abort();
//设置超时时间
set_time_limit(60 * 15);

function submitForm(){
    $snoopy = new Snoopy;

    $formvars["loginname"] = "admin";
    $formvars["password"] = "21218CCA77804D2BA1922C33E0151105";

    $data = json_encode($formvars);

    $action = "http://118.178.224.54/api/Account/Login";

    $snoopy->agent = "(compatible; MSIE 4.01; MSN 2.5; AOL 4.0; Windows 2000)"; //伪装浏览器
    $snoopy->referer = "http://118.178.224.54"; //伪装来源页地址 http_referer
    $snoopy->rawheaders["Pragma"] = "no-cache"; //cache 的http头信息
    $snoopy->rawheaders["X_FORWARDED_FOR"] = "127.0.0.1"; //伪装ip

    $snoopy->submit($action,$data);
    $data = $snoopy->results;
    return $data;
}

echo submitForm();

























