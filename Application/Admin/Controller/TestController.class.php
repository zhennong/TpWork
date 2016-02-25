<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/25/16
 * Time: 5:25 PM
 */

namespace Admin\Controller;


use Common\Tools;

class TestController extends AdminController
{
    public function index(){
        $this->redirect("test1");
    }

    public function test1(){
        $all_area = D('Area')->select();
        $this->assign('all_area',$all_area);
        $this->display();
    }
}