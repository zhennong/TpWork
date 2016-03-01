<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/29/16
 * Time: 4:51 PM
 */

namespace Admin\Model;


class AuthGroupModel extends AdminModel
{
    //自动完成
    protected $_auto = [
        ['create_time','time',1,'function']
    ];

    //自动验证
    protected $_validate = [
        ['title','require','标题必须填写！'],
        ['title','','名称已经存在！',0,'unique'],
    ];
}