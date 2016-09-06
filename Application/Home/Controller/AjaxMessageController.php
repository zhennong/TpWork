<?php
namespace Home\Controller;

header('Access-Control-Allow-Origin: *');

use Common\Controller\CommonController;
use Common\Tools;
use Home\Api\ApiAppKnow;

class AjaxMessageController extends CommonController {
    public function index(){
        $this->display();
    }
}