<?php
namespace Home\Controller;
use Common\Controller\CommonController;
use Common\Tools;

class HelpController extends CommonController {

    /**
     * 隐私声明
     */
    public function privacy(){
        $this->display();
    }
}