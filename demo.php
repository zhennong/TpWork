<?php
//header('Access-Control-Allow-Origin: *');
//header('Content-Type: text/event-stream');
//header('Cache-Control: no-cache');

$action = $_GET['action'];
$uid = $_GET['userid'];

$status = 0;

//消息处理
switch ($action){
    case 'mess_tips';
//        $sql = find_sql("destoon_appknow_message_reply",$uid);
//        $status = $model->list_query($sql);
        $status = 0;
        break;
    case 'mess_invite';

        //专家邀请

        break;
    case 'mess_agree';
//        $sql = find_sql("destoon_appknow_message_agree",$uid);
//        $status = $model->list_query($sql);
        break;
    case 'mess_attention';
//        $sql = find_sql("destoon_appknow_message_attention",$uid);
//        $status = $model->list_query($sql);
        break;
    default:
        break;
}

if($status > 0){
    echo "data:{$status}\n\n";
}else{
    echo "data:{$status}\n\n";
}
flush();

