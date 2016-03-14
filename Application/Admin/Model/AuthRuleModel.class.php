<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/1/16
 * Time: 2:56 PM
 */

namespace Admin\Model;


class AuthRuleModel extends AdminModel
{
    protected $_auto = [
        ['create_time','time',1,'function'],
    ];

    protected $_validate = [
        ['name','require','标志必须！'],
        ['title','require','标题必须！'],
        ['name','','标志已经存在！',0,'unique'],
    ];
}