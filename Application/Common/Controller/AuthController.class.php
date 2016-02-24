<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 6:06 PM
 */

namespace Common\Controller;


class AuthController extends CommonController
{
    public $not_check = [
        'Admin/Index/index',"Admin/Public/login"
    ];

    public function _initialize(){
        if(!session('user_id')){
            $this->error("尚未登录",U("Admin/Public/login"));
        }
    }
}