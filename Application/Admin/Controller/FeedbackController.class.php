<?php
namespace Admin\Controller;

use Common\Tools;

class FeedbackController extends AuthController {

    /**
     * 意见回馈列表
     */
    public function feedback_list(){
        $data = M('FeedBack')->order('id desc')->select();
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * 删除意见
     */
    public function feedback_del(){
        $id = I('get.id');
        if(!empty($id)){
            $map['id'] = $id;
            $resutl = M('FeedBack')->where($map)->delete();
            if($resutl){
                $this->ajaxReturn(1);
            }else{
                $this->ajaxReturn(0);
            }
        }else{
            $this->ajaxReturn(2);
        }
    }
}