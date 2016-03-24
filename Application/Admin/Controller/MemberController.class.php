<?php
namespace Admin\Controller;

class MemberController extends AuthController {

    /**
     * 会员列表
     */
    public function member_list(){
        $this->display();
    }

    /**
     * 会员添加/更新
     */
    public function member_manage(){
        $this->display();
    }

    /**
     * 会员删除
     */
    public function member_delete(){
        $this->display();
    }

}