<?php
namespace Home\Controller;
use Common\Controller\CommonController;
use Common\Tools;
use Home\Api\ApiAppKnow;

class MessageController extends CommonController {

    //消息推送
    public function index(){
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        
        $time = date('r');
        echo "data: The server time is: {$time}\n\n";
        flush();
    }

    public function sql(){

        //问题回复
        $sql = "select a.addtime,a.isread,b.mobile,c.nickname from destoon_appknow_message_agree as a LEFT JOIN destoon_ucenter_member as b on a.from_uid = b.userid LEFT JOIN destoon_appknow_member_profile as c on c.userid = b.userid where to_uid = 22 ORDER BY a.addtime desc limit 10";

        //点赞消息
        $sql = "SELECT a.addtime,a.isread,b.mobile,c.nickname FROM `destoon_appknow_message_agree` as a LEFT JOIN destoon_ucenter_member as b on a.from_uid = b.userid LEFT JOIN destoon_appknow_member_profile as c on c.userid = b.userid where to_uid = 22 ORDER BY a.addtime desc limit 10";

        //关注消息
        $sql = "SELECT a.addtime,a.isread,b.mobile,c.nickname FROM `destoon_appknow_message_attention` as a LEFT JOIN destoon_ucenter_member as b on a.from_uid = b.userid LEFT JOIN destoon_appknow_member_profile as c on c.userid = b.userid where to_uid = 22 ORDER BY a.addtime desc limit 10";

    }
}