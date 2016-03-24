<?php
namespace Admin\Controller;

use Common\Tools;

class IndexController extends AuthController {
    public function index(){
        $this->display();
    }

    /**
     * 用户详情
     */
    public function userInfo()
    {
        $userInfo = $this->admin_user;
        $this->assign(['userInfo'=>$userInfo]);
        $this->display();
    }
}