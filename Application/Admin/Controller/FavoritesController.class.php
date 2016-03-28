<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/24/16
 * Time: 6:01 PM
 */

namespace Admin\Controller;


class FavoritesController extends AuthController
{
    public function favoriteList()
    {
        $fansList = M('MemberFavourite')->select();
        foreach($fansList as $k => $v){
            $user_info = D('Member')->field(['userid','mobile'])->where(['userid'=>$v['uid']])->find();
            $fansList[$k]['user_info'] = $user_info;
        }
        $this->assign(['fansList'=>$fansList]);
        $this->display();
    }
}