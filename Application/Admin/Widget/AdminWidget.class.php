<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/25/16
 * Time: 3:04 PM
 */

namespace Admin\Widget;


use Admin\Controller\AdminController;

class AdminWidget extends AdminController
{
    public function test($id,$name){
        echo $id.':'.$name;
    }
}