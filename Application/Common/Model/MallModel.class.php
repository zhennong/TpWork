<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/25/16
 * Time: 5:42 PM
 */

namespace Common\Model;


use Common\Tools;

abstract class MallModel extends CommonModel
{
    protected $tablePrefix;
    protected $connection;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->tablePrefix = C('DATABASE_MALL_TABLE_PREFIX');
        $this->connection = C('DATABASE_MALL');
    }
}