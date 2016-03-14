<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/11
 * Time: 17:50
 */

namespace Home;


abstract class Model extends \Think\Model
{
    public $now,$mod;
    public function __construct(){
        $this->now = time();
        //实例化空模型
        $this->mod = D();
        $this->mod->db(1,C('DATABASE_MALL'));
    }

    //查询SQL(SELECT)
    public function list_query($sql){
        $data = $this->mod->query($sql);
        return $data;
    }

    //执行SQL（UPDATE/INSERT/DELETE）
    public function execute($sql){
        $data = $this->mod->execute($sql);
        return $data;
    }
}