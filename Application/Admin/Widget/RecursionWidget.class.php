<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/25/16
 * Time: 3:10 PM
 */
/**
 * 一些递归显示操作
 */
namespace Admin\Widget;


use Common\Tools;

class RecursionWidget extends AdminWidget
{
    public function showAuth($authTree,$sign=''){
        $this->assign(['authTree'=>$authTree,'sign'=>$sign]);
//        Tools::_vp($authTree,0,2);
        $this->display("Widget:showAuth");
    }

    public function cate_a_m_list($cate_tree,$sign=''){
        $this->assign(['cate_tree'=>$cate_tree,'sign'=>$sign]);
        $this->display("Widget:cate_a_m_list");
    }
}