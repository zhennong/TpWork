<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 12:21 PM
 */

namespace Common\Controller;
use Think\Controller;


abstract class CommonController extends Controller
{
    public $route=''; //  路由地址
    protected $MallDb;
    protected $malldb; //商城数据库链接

    public function _initialize(){
        // 获取路由地址
        $this->route = MODULE_NAME . "/" . CONTROLLER_NAME . "/" . ACTION_NAME;
        M()->execute("SET NAMES UTF8");

        $this->getMallDb();
    }

    /**
     * 获取商城数据库连接
     */
    private function getMallDb(){
        if(!$this->MallDb){
            $MallDb = new MallDb();
            $this->MallDb = $MallDb;
            $this->malldb = $MallDb->db;
        }
    }
}