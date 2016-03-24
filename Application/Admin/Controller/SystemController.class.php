<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 6:00 PM
 */

namespace Admin\Controller;


use Common\Tools;

class SystemController extends AuthController
{
    public function index(){
        $this->display();
    }

    /**
     * 用户设置
     */
    public function userSetting()
    {
        $this->display();
    }

    /**
     * 数据备份
     */
    public function dataBackup()
    {
        $sql = "SHOW TABLES";
        $tables = D()->query($sql);
        foreach($tables as $k => $v){
            $x = $v["tables_in_".C('DB_NAME')];
            if(preg_match("/^".C('DB_PREFIX')."/",$x)){
                $select_tables[] = $x;
            }
            if(preg_match("/^".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_/",$x)){
                $select_tables[] = $x;
            }
        }
        $this->assign(['select_tables'=>$select_tables]);
        $this->display();
    }

    /**
     * 数据还原
     */
    public function dataReduction()
    {
        $this->display();
    }
}