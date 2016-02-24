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
    public function login(){
        $this->display();
    }
}