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
        $db_name = C('DB_NAME');
        $D = D();
        if(IS_POST){
            // 备份
            /*$connectid = mysql_connect(C('DB_HOST'),C('DB_USER'),C('DB_PWD'));
            $connectid = $D;
            $connectid = $D->db(1);
            mysql_query("set names utf8",$connectid);
            $backup_dir = APP_ROOT."/Data/sql/".date("Y-m-d H:i:s")."/";
            $select_tables = I("post.select_tables");
            $DbBak = new DbBak($connectid,$backup_dir);
            foreach($select_tables as $k => $v){
                $tables[] = $v[0];
            }
            if(count($tables)>0){
                $DbBak->backupDb($db_name,$tables);
            }*/

            $select_tables = I("post.select_tables");
            foreach($select_tables as $k => $v){
                $tables[] = $v[0];
            }
            $backup_dir = APP_ROOT."/Data/sql/".date("Y-m-d H:i:s")."/";
            $size = Tools\wodrow\TablesBackup::backupTables($tables,$backup_dir);
            echo $size;
            exit();
        }

        $sql = "SHOW TABLES";
        $tables = $D->query($sql);
        $sql = "use information_schema";
        $D->execute($sql);
        foreach($tables as $k => $v){
            static $i = 0;
            $table = $v["tables_in_".C('DB_NAME')];
            if(preg_match("/^".C('DB_PREFIX')."/",$table)||preg_match("/^".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_/",$table)){
                // 获取表信息
                $sql = "select concat(round(sum(data_length/1024/1024),2),'MB') as data, table_name, table_rows from tables where table_schema='{$db_name}' and table_name='{$table}'";
                $table_size_info = $D->query($sql);

                $select_tables[$i]['table_name'] = $table_size_info[0]['table_name'];
                $select_tables[$i]['table_size'] = $table_size_info[0]['data'];
                $select_tables[$i]['table_count'] = $table_size_info[0]['table_rows'];
            }
            $i++;
        }
        $D->execute("use {$db_name}");
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