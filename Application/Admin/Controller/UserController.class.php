<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/24/16
 * Time: 12:01 PM
 */

namespace Admin\Controller;


use Common\Tools;

class UserController extends AuthController
{
    public function user_index(){
        $this->display('Index:index');
    }

    /**
     * 用户管理
     */
    public function user_manage(){
        if(IS_POST){
            Tools::_vp($_POST);
            exit();
        }
        $user_list = $this->getAdminUserDetail(null,['group_detail']);
        $this->assign(['user_list'=>$user_list]);
        Tools::_vp(D('AdminUser')->select());
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
        $node_tree = Tools::list2tree($this->authAll);
        $this->assign(['node_tree'=>$node_tree]);
        $this->display();
    }
}