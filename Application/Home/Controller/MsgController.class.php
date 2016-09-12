<?php
namespace Home\Controller;
header('Access-Control-Allow-Origin: *');
use Common\Controller\CommonController;
use Common\Tools;
use Home\Api\ApiAppKnow;


class MsgController extends CommonController {

    /**
     *  消息推送接口 【新版本】
     *  @date 2016.09.07
     */
    public function index(){
        $jsoncallback = I('get.jsoncallback');
        $api = new ApiAppKnow();
        $show['status'] = 200;
        $info = I('get.');
        switch (I('get.action')) {
            //获取推送消息列表
            case 'get_msg':
                $list = D('Msg')->getMessList($info);
                $show['msg_list'] = $list;
                break;

            //判断消息是否已读
            case 'is_read':
                $show['status'] = D('Msg')->getMessStatus($info);
                break;

            default:
                $show['status'] = 201;
                break;
        }

        // 状态信息
        $show['msg'] = $api->status[$show['status']];
        $show_msgs = $jsoncallback . "(" . json_encode($show) . ")";
        echo $show_msgs;
        exit();
    }
}