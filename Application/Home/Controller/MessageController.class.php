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

//                $sql = "select * from destoon_appknow_message_answer limit 3";
//                $data = $api->list_query($sql);
//                $status = json_encode($data);

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

    /**
     * 获取消息数据
     */
    public function get_mess_data(){
        $info = I('get.');
        $api = new ApiAppKnow();
        $mess_data = '';
        switch ($info['action']){
            //获取回复消息
            case 'get_mess_tips':
                $data = $api->getMessList($info);
                
                foreach ($data AS $k=>$v){
                    $data[$k]['addtime'] = date("Y-m-d",$v['addtime']);
                    $data[$k]['mobile'] = $api->mobileHide($v['mobile']);
                    $data[$k]['content'] = Tools::msubstr($v['content'],0,25);
                }
                $mess_data = json_encode($data);

                break;

            //获取邀请消息
            case 'get_mess_invite':
                $info = $_GET;
                $data = $api->getMessList($info);
                foreach ($data AS $k=>$v){
                    $data[$k]['addtime'] = date("Y-m-d",$v['addtime']);
                    $data[$k]['mobile'] = $api->mobileHide($v['mobile']);
                    $data[$k]['content'] = Tools::msubstr($v['content'],0,25);
                }
                $mess_data = json_encode($data);
                break;

            //获取点赞消息
            case 'get_mess_agree':
                $info = $_GET;
                $data = $api->getMessList($info);
                foreach ($data AS $k=>$v){
                    $data[$k]['addtime'] = date("Y-m-d",$v['addtime']);
                    $data[$k]['mobile'] = $api->mobileHide($v['mobile']);
                    $data[$k]['content'] = Tools::msubstr($v['content'],0,25);
                }
                $mess_data = json_encode($data);
                break;

            //获取关注消息
            case 'get_mess_attention':
                $info = $_GET;
                $data = $api->getMessList($info);
                foreach ($data AS $k=>$v){
                    $data[$k]['addtime'] = date("Y-m-d",$v['addtime']);
                    $data[$k]['mobile'] = $api->mobileHide($v['mobile']);
                }
                $mess_data = json_encode($data);
                break;
        }

        //判断是否有新消息
        if(!empty($data)){
            echo "data:{$mess_data}\n\n";
        }else{
            echo "data:0\n\n";
        }

        flush();
    }
}