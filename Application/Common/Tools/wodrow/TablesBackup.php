<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/25/16
 * Time: 4:04 PM
 */

namespace Common\Tools\wodrow;


use Common\Tools;

class TablesBackup
{
    /**
     * 获取数据表生成语句
     */
    public static function getCreateSql($table)
    {
        $M = M();
        $rs = $M->query("SHOW CREATE TABLE `{$table}`");
        $tbSql = preg_replace("#CREATE(.*)\\s+TABLE#","CREATE TABLE",$rs[0]['create table']);
        $sql = "DROP TABLE IF EXISTS `{$rs[0]['table']}`;\r\n{$tbSql};\r\n";
        return $sql;
    }

    /**
     * 获取数据表数据插入语句
     */
    public static function getInsertSql($table)
    {
        $M = M();
        $rs=$M->query("select * from `{$table}`");
        $arrAll[]="\r\nDELETE FROM {$table};";
        foreach ($rs as $k => &$v){
            $arrValues = array();
            foreach($v as $key=>$val)
            {
                if(is_numeric($val)){
                    $arrValues[]=$val;
                }else if(is_null($val)){
                    $arrValues[]='NULL';
                }else{
                    $arrValues[]="'".addslashes($val)."'";
                }
            }
            $arrAll[] = "INSERT INTO `{$table}` VALUES (".implode(',',$arrValues).");";
        }
        return implode("\r\n",$arrAll)."\r\n\r\n";
    }

    /**
     * 获取备份数据表语句
     */
    public static function getTableBackupSql($table)
    {
        $sql = self::getCreateSql($table) . self::getInsertSql($table);
        return $sql;
    }

    /**
     * 分表备份
     * @param $tables
     * @param string $dir
     */
    public static function backupTables($tables=[],$dir="/")
    {
        $size = 0;
        foreach($tables as $k => $v){
            $sql = self::getTableBackupSql($v);
            $filename = $dir."{$v}.sql";
            Tools::createDir($dir);
            $size += file_put_contents($filename, $sql);
            if($size){}else{
                E('操作异常');
            }
        }
        return $size;
    }
}