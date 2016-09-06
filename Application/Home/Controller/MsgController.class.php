<?php
namespace Home\Controller;
use Common\Controller\CommonController;
use Common\Tools;
use Home\Api\ApiAppKnow;
class MsgController extends CommonController {

    /**
     * 消息列表模块
     */
    public function index(){
        $api = new ApiAppKnow();
        $list['mess_list'] = $api->getMessList(I('get.'));
        $data = '('.json_encode($list).')';
        echo $data;
    }
}