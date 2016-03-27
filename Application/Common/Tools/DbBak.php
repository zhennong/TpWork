<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/25/16
 * Time: 11:34 AM
 */
/*1、实例化DbBak需要告诉它两件事：数据服务器在哪里($connectid)、备份到哪个目录($backupDir)：
    require_once('DbBak.php');
    require_once('TableBak.php');
    $connectid = mysql_connect('localhost','root','123456');
    $backupDir = 'data';
    $DbBak = new DbBak($connectid,$backupDir);
    2、然后就可以开始备份数据库了，你不仅能够指定备份那个数据库，而且能详细设置只备份那几个表：
    2.1如果你想备份mybbs库中的所有表，只要这样：
    $DbBak->backupDb('mybbs');
    2.2如果你只想备份mybbs库中的board、face、friendlist表，可以用一个一维数组指定：
    $DbBak->backupDb('mybbs',array('board','face','friendsite'));
    2.3如果只想备份一个表，比如board表：
    $DbBak->backupDb('mybbs','board');
    3，数据恢复：
    对于2.1、2.1、2.3三种情况，只要相应的修改下语句，把backupDb换成restoreDb就能实现数据恢复了：
    $DbBak->restoreDb('mybbs');
    SQL代码
    $DbBak->restoreDb('mybbs',array('board','face','friendsite'));
    PHP代码
    $DbBak->restoreDb('mybbs','board');
    备份mybbs数据库：
    SQL代码
    //example 1 backup:
    require_once('DbBak.php');
    require_once('TableBak.php');
    $connectid = mysql_connect('localhost','root','123456');
    $backupDir = 'data';
    $DbBak = new DbBak($connectid,$backupDir);
    $DbBak->backupDb('mybbs');
    恢复mybbs数据库：
    复制代码代码如下:
    require_once('DbBak.php');
    require_once('TableBak.php');
    $connectid = mysql_connect('localhost','root','123456');
    $backupDir = 'data';
    $DbBak = new DbBak($connectid,$backupDir);
    $DbBak->restoreDb('mybbs');*/

namespace Common\Tools;


class DbBak {
    var $_mysql_link_id;
    var $_dataDir;
    var $_tableList;
    var $_TableBak;
    function DbBak($_mysql_link_id,$dataDir)
    {
        ( (!is_string($dataDir)) || strlen($dataDir)==0) && die('error:$datadir is not a string');
        !is_dir($dataDir) && mkdir($dataDir);
        $this->_dataDir = $dataDir;
        $this->_mysql_link_id = $_mysql_link_id;
    }
    function backupDb($dbName,$tableName=null)
    {
        ( (!is_string($dbName)) || strlen($dbName)==0 ) && die('$dbName must be a string value');
        //step1:选择数据库：
        mysql_select_db($dbName);
        //step2:创建数据库备份目录
        $dbDir = $this->_dataDir.DIRECTORY_SEPARATOR.$dbName;
        !is_dir($dbDir) && mkdir($dbDir);
        //step3:得到数据库所有表名 并开始备份表
        $this->_TableBak = new TableBak($this->_mysql_link_id,$dbDir);
        if(is_null($tableName)){//backup all table in the db
            $this->_backupAllTable($dbName);
            return;
        }
        if(is_string($tableName)){
            (strlen($tableName)==0) && die('....');
            $this->_backupOneTable($dbName,$tableName);
            return;
        }
        if (is_array($tableName)){
            foreach ($tableName as $table){
                ( (!is_string($table)) || strlen($table)==0 ) && die('....');
            }
            $this->_backupSomeTalbe($dbName,$tableName);
            return;
        }
    }
    function restoreDb($dbName,$tableName=null){
        ( (!is_string($dbName)) || strlen($dbName)==0 ) && die('$dbName must be a string value');
        //step1:检查是否存在数据库 并连接：
        @mysql_select_db($dbName) || die("the database $dbName dose not exists");
        //step2:检查是否存在数据库备份目录
        $dbDir = $this->_dataDir.DIRECTORY_SEPARATOR.$dbName;
        !is_dir($dbDir) && die("$dbDir not exists");
        //step3:start restore
        $this->_TableBak = new TableBak($this->_mysql_link_id,$dbDir);
        if(is_null($tableName)){//backup all table in the db
            $this->_restoreAllTable($dbName);
            return;
        }
        if(is_string($tableName)){
            (strlen($tableName)==0) && die('....');
            $this->_restoreOneTable($dbName,$tableName);
            return;
        }
        if (is_array($tableName)){
            foreach ($tableName as $table){
                ( (!is_string($table)) || strlen($table)==0 ) && die('....');
            }
            $this->_restoreSomeTalbe($dbName,$tableName);
            return;
        }
    }
    function _getTableList($dbName)
    {
        $tableList = array();
        $result=mysql_list_tables($dbName,$this->_mysql_link_id);
        for ($i = 0; $i < mysql_num_rows($result); $i++){
            array_push($tableList,mysql_tablename($result, $i));
        }
        mysql_free_result($result);
        return $tableList;
    }
    function _backupAllTable($dbName)
    {
        foreach ($this->_getTableList($dbName) as $tableName){
            $this->_TableBak->backupTable($tableName);
        }
    }
    function _backupOneTable($dbName,$tableName)
    {
        !in_array($tableName,$this->_getTableList($dbName)) && die("指定的表名{$tableName}在数据库中不存在");
        $this->_TableBak->backupTable($tableName);
    }
    function _backupSomeTalbe($dbName,$TableNameList)
    {
        foreach ($TableNameList as $tableName){
            !in_array($tableName,$this->_getTableList($dbName)) && die("指定的表名{$tableName}在数据库中不存在");
        }
        foreach ($TableNameList as $tableName){
            $this->_TableBak->backupTable($tableName);
        }
    }
    function _restoreAllTable($dbName)
    {
        //step1:检查是否存在所有数据表的备份文件 以及是否可写：
        foreach ($this->_getTableList($dbName) as $tableName){
            $tableBakFile = $this->_dataDir.DIRECTORY_SEPARATOR
                . $dbName.DIRECTORY_SEPARATOR
                . $tableName.DIRECTORY_SEPARATOR
                . $tableName.'.sql';
            !is_writeable ($tableBakFile) && die("$tableBakFile not exists or unwirteable");
        }
        //step2:start restore
        foreach ($this->_getTableList($dbName) as $tableName){
            $tableBakFile = $this->_dataDir.DIRECTORY_SEPARATOR
                . $dbName.DIRECTORY_SEPARATOR
                . $tableName.DIRECTORY_SEPARATOR
                . $tableName.'.sql';
            $this->_TableBak->restoreTable($tableName,$tableBakFile);
        }
    }
    function _restoreOneTable($dbName,$tableName)
    {
        //step1:检查是否存在数据表:
        !in_array($tableName,$this->_getTableList($dbName)) && die("指定的表名{$tableName}在数据库中不存在");
        //step2:检查是否存在数据表备份文件 以及是否可写：
        $tableBakFile = $this->_dataDir.DIRECTORY_SEPARATOR
            . $dbName.DIRECTORY_SEPARATOR
            . $tableName.DIRECTORY_SEPARATOR
            . $tableName.'.sql';
        !is_writeable ($tableBakFile) && die("$tableBakFile not exists or unwirteable");
        //step3:start restore
        $this->_TableBak->restoreTable($tableName,$tableBakFile);
    }
    function _restoreSomeTalbe($dbName,$TableNameList)
    {
        //step1:检查是否存在数据表:
        foreach ($TableNameList as $tableName){
            !in_array($tableName,$this->_getTableList($dbName)) && die("指定的表名{$tableName}在数据库中不存在");
        }
        //step2:检查是否存在数据表备份文件 以及是否可写：
        foreach ($TableNameList as $tableName){
            $tableBakFile = $this->_dataDir.DIRECTORY_SEPARATOR
                . $dbName.DIRECTORY_SEPARATOR
                . $tableName.DIRECTORY_SEPARATOR
                . $tableName.'.sql';
            !is_writeable ($tableBakFile) && die("$tableBakFile not exists or unwirteable");
        }
        //step3:start restore:
        foreach ($TableNameList as $tableName){
            $tableBakFile = $this->_dataDir.DIRECTORY_SEPARATOR
                . $dbName.DIRECTORY_SEPARATOR
                . $tableName.DIRECTORY_SEPARATOR
                . $tableName.'.sql';
            $this->_TableBak->restoreTable($tableName,$tableBakFile);
        }
    }
}