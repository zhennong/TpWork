<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 10:46 AM
 */

namespace Admin\Controller;



use Common\Controller\CommonController;

abstract class AdminController extends CommonController
{
    public $admin_user = [];//后台用户

    public function _initialize(){
        parent::_initialize();
        $admin_user = session('admin_user');
        if($admin_user){
            $this->admin_user = $admin_user;
        }
    }
}