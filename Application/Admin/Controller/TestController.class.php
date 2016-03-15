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
    public function index()
    {
        $this->redirect("test1");
    }

    /**
     * datatables server
     */
    public function test1()
    {
        $all_area = D('Area')->select();
        $this->assign('all_area', $all_area);
        $this->display();
    }

    public function test2()
    {
        $all_area = D('Area')->field(['areaid', 'areaname'])->limit(10)->select();
        $count = count(D('Area')->field(['areaid', 'areaname'])->select());
        //获取Datatables发送的参数 必要
        $draw = $_GET['draw'];//这个值作者会直接返回给前台
        foreach($all_area as $k => $v){
            $z[$k][] = $v['areaid'];
            $z[$k][] = $v['areaname'];
        }
        $x = [
            "draw" => $draw,
            "recordsTotal" => $count,
            "recordsFiltered" => $count,
            "data" => $z,
        ];
        Tools::_vp($_GET,0,2);
        $y = json_encode($x);
        echo $y;
    }
}