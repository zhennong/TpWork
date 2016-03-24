<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 6:00 PM
 */

namespace Admin\Controller;


class SystemController extends AuthController
{
    public function index(){
        $this->display();
    }

    /**
     * 用户设置
     */
    public function userSetting()
    {
        $this->display();
    }
}