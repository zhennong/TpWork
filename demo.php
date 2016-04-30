<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$sql_count = "select count(*) from message where from_uid = {uid} and isread = 1 limit 10";

if($sql_count > 0){
    $d = date('s',time());
    echo "data:{$d}\n\n";
    flush();
}else{
    //无更新
}



