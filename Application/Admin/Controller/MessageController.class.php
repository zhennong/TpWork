<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/24/16
 * Time: 8:52 AM
 */

namespace Admin\Controller;

use Common\Controller\CommonController;
use Common\Tools;

class MessageController extends AuthController
{
    /**
     * 系统消息列表
     */
    public function message(){
        $sql = "SELECT a.*,a.addtime AS pubdate,b.* FROM destoon_appknow_message_sys AS a LEFT JOIN destoon_ucenter_member AS b ON a.to_uid = b.userid ORDER BY a.id DESC";
        $data = M('')->query($sql);

        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 删除系统消息
     */
    public function message_delete(){
        $id = I('get.id');
        if (!empty($id)){
            $result = D('message_sys')->delete($id);
            if ($result) {
                $this->ajaxReturn(1);
            } else {
                $this->ajaxReturn(0);
            }
        } else {
            $this->ajaxReturn(2); //参数异常
        }
    }

    /**
     * 推送消息
     */
    public function message_add(){
        $opt = I('get.action');
        if($opt == 'add'){
            $data = array();
            $data['title'] = I('get.title');
            $data['content'] = I('get.content');
            if(I('get.range') == 0){
                $data['to_uid'] = 0;
            }else{
                $data['to_uid'] = I('get.to_uid');
            }
            $data['addtime'] = time();

            $result = M('message_sys')->add($data);
            if($result){
                if(I('get.range') == 0){
                    //消息推送全局
                    Tools::Jpush_Send('all',$data['content']);
                }
                $this->ajaxReturn(1);
            }else{
                $this->ajaxReturn(0);
            }
        }
        $this->display();
    }

    /**
     * 获取会员ID
     */
    public function getMemberID(){
        $map['mobile'] = I('get.mobile');	//账号
        $result = D('Member')->field('userid')->where($map)->select();
        if(!empty($result)){
            $uid = $result[0]['userid'];
            $this->ajaxReturn($uid);	//存在
        }else{
            $this->ajaxReturn(0);   //不存在
        }
    }
}