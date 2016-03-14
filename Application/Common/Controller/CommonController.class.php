<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 12:21 PM
 */

namespace Common\Controller;
use Think\Controller;


abstract class CommonController extends Controller
{
    public $route=''; //  路由地址

    public function _initialize(){
        // 获取路由地址
        $this->route = MODULE_NAME . "/" . CONTROLLER_NAME . "/" . ACTION_NAME;
        M()->execute("SET NAMES UTF8");
    }
}