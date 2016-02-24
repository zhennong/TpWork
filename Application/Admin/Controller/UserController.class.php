<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/24/16
 * Time: 12:01 PM
 */

namespace Admin\Controller;


class UserController extends AuthController
{
    public function index(){
        echo 123;
    }

    /**
     * 用户管理
     */
    public function user_manage(){
        $user_list = M('Admin')->select();
        $this->assign(['user_list'=>$user_list]);
        $this->display();
    }

    /**
     * 用户组管理
     */
    public function group_manage(){
        $group_list = $this->getAuthGroupDetail();
        $this->assign(['group_list'=>$group_list]);
        $this->display();
    }

    /**
     * 节点管理
     */
    public function node_manage(){
        $node_list = M('AuthRule')->select();
        $this->assign(['node_list'=>$node_list]);
        $this->display();
    }
}