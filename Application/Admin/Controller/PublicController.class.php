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

class PublicController extends CommonController
{
    /**
     * 登录
     */
    public function login(){
        $Admin = M('Admin');
        if(IS_POST){
            $x = $Admin->where(['account'=>I('post.account'),'password'=>md5(I('post.password'))])->select();
            if(count($x)==1){
                session('admin_user',$x[0]);
                header("location:".Tools::get_redirect_url());
            }
        }
        $this->display();
    }

    /**
     * 注销登录
     */
    public function logout(){
        session(null);
        $this->success("注销成功",U("Admin/Public/login"));
    }
}