<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/24/16
 * Time: 5:59 PM
 */

namespace Admin\Controller;


use Common\Tools;

class FansController extends AuthController
{
    public function fansList()
    {
        $fansList = M('MemberFans')->select();
        foreach($fansList as $k => $v){
            $attention_info = D('Member')->field(['userid','mobile'])->where(['userid'=>$v['attention_uid']])->find();
            $fansList[$k]['attention_info'] = $attention_info;
            $fans_info = D('Member')->field(['userid','mobile'])->where(['userid'=>$v['fans_uid']])->find();
            $fansList[$k]['fans_info'] = $fans_info;
        }
        $this->assign(['fansList'=>$fansList]);
        $this->display();
    }
}