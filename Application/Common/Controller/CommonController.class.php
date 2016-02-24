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
    public $route='';

    public function _initialize(){
        $this->route = MODULE_NAME . "/" . CONTROLLER_NAME . "/" . ACTION_NAME;
    }
}