<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/25/16
 * Time: 11:24 AM
 */
/*使用方法：

require_once("backdata.class.php");
$link = @mysql_connect("localhost","数据库名","密码") or die ('Could not connect to server.');
mysql_query("use cms",$link);
mysql_query("set names utf8",$link);

$dbbck = new backupData($link);//实例化它，只要一个链接标识就行了

//备份数据时，如想备份一个数据库中的所有表，你可这样写：
$dbbck->backupTables("cms","./",array('*'));

//备份数据时，如想备份一个数据库中的仅一个表时，你可这样写：
$dbbck->backupTables("cms","./",array('user'));

//备份数据时，如想备份一个数据库中的多个表时，你可这样写：
$dbbck->backupTables("cms","./",array('user','acl','informatoin'));

//注解：$dbbck->backupTables("参1","参2",array());中，
参1为：数据库名，
参2为：要存放备份数据的位置（即目录地址）
第三个为：你要保存那些表 */

namespace Common\Tools;


class DataBackup
{
    private    $mysql_link;//链接标识
    private    $dbName;    //数据库名
    private    $dataDir;    //数据所要存放的目录
    private    $tableNames;//表名

    public function __construct($mysql_link){
        $this->mysql_link = $mysql_link;
    }
    public function backupTables($dbName,$dataDir,$tableNames){//开始备份
        $this->dbName  = $dbName;
        $this->dataDir  = $dataDir;
        $this->tableNames = $tableNames;
        $tables=$this->delarray($this->tableNames);
        $sqls='';
        foreach($tables as $tablename){
            if($tablename==''){//表不存在时
                continue;
            }

            //************************以下是形成SQL的前半部分**************
            //如果存在表，就先删除
            $sqls .= "DROP TABLE IF EXISTS $tablename;\n";
            //读取表结构
            $rs = mysql_query("SHOW CREATE TABLE $tablename",$this->mysql_link);
            $row=mysql_fetch_row($rs);
            //获得表结构组成SQL
            $sqls.=$row['1'].";\n\n";
            unset($rs);
            unset($row);

            //************************以下是形成SQL的后半部分**************
            //查寻出表中的所有数据
            $rs=mysql_query("select * from $tablename",$this->mysql_link);
            //表的字段个数
            $field=mysql_num_fields($rs);
            //形成此种SQL语句:"INSERT INTO `groups` VALUES('1499e0ca25988d','主任','','0');"
            while($rows=mysql_fetch_row($rs)){
                $comma='';//逗号
                $sqls.="INSERT INTO `$tablename` VALUES(";
                for($i=0;$i<$field;$i++){
                    $sqls.=$comma."'".$rows[$i]."'";
                    $comma=',';
                }
                $sqls.=");\n\n\n";
            }
        }
        $backfilepath=$this->dataDir.date("Ymdhis",time()).'.sql';

        //写入文件
        $filehandle = fopen($backfilepath, "w");
        fwrite($filehandle, $sqls);
        fclose($filehandle);
    }
    private function delarray($array){    //处理传入进来的数组
        foreach($array as $tables){
            if($tables=='*'){    //所有的表(获得表名时不能按常规方式来组成一个数组)
                $newtables=mysql_list_tables($this->dbName,$this->mysql_link);
                $tableList = array();
                for ($i = 0; $i < mysql_numrows($newtables); $i++){
                    array_push($tableList,mysql_tablename($newtables, $i));
                }
                $tableList=$tableList;
            }else{
                $tableList=$array;
                break;
            }
        }
        return $tableList;
    }
}