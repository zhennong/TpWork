<?php
namespace Home\Controller;

header('Access-Control-Allow-Origin: *');
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

use Common\Controller\CommonController;
use Common\Tools;
use Home\Api\ApiAppKnow;

class MessageController extends CommonController {

    //消息推送
    public function index(){
        $action = I('get.action');
        $uid = I('get.userid');
        $status = 0;

        $api = new ApiAppKnow();

        //消息处理
        switch ($action){
            case 'mess_tips':
                $count = $api->mess_count("appknow_message_reply",$uid);
                $status = $count[0]['count'];
                break;
            case 'mess_invite':
                $count = $api->mess_count("appknow_message_invite",$uid);
                $status = $count[0]['count'];
                break;
            case 'mess_agree':
                $count = $api->mess_count("appknow_message_agree",$uid);
                $status = $count[0]['count'];
                break;
            case 'mess_attention':
                $count = $api->mess_count("appknow_message_attention",$uid);
                $status = $count[0]['count'];
                break;
            default:
                break;
        }

        //判断是否有新消息
        if($status > 0){
            echo "data:{$status}\n\n";
        }else{
            echo "data:{$status}\n\n";
        }
        flush();
    }
}