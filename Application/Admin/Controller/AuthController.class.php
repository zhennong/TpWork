<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 6:06 PM
 */

namespace Admin\Controller;


use Common\Tools;
use Think\Auth;

class AuthController extends AdminController
{
    public $not_check = [
        'Admin/Index/index'
    ];

    public function _initialize(){
        parent::_initialize();

        //session不存在时，不允许直接访问
        if(!$this->admin_user['id']){
            Tools::set_redirect_url(Tools::get_url());
            $this->error("尚未登录",U("Admin/Public/login"));
        }

        //session存在时，不需要验证的权限
        if(in_array($this->route,$this->not_check)){
            return true;
        }

        //下面代码动态判断权限
        $Auth = new Auth();
        if (!$Auth->check($this->route, $this->admin_user['id']) && $this->admin_user['id'] != 1) {
            $this->error('没有权限',U('Admin/Index/index'));
        }
    }
}