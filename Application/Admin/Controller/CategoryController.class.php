<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/2/16
 * Time: 2:21 PM
 */

namespace Admin\Controller;


class CategoryController extends AuthController
{
    public function cate_agricultural_medicine(){
        $cates = M('Category')->select();
        $this->assign(['cates'=>$cates]);
        $this->display();
    }
}