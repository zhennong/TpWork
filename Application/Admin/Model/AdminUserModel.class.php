<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/26/16
 * Time: 6:54 PM
 */

namespace Admin\Model;


class AdminUserModel extends AdminModel
{
    protected $tableName = 'admin';

    //自动完成
    protected $_auto = [
        ['password','md5',3,'function'],
        ['login_time','time',3,'function'],
        ['login_ip','get_client_ip',3,'function'],
        ['create_time','time',1,'function']
    ];

    //自动验证
    protected $_validate = [
        ['account','require','帐号必须！'],
        ['account','','帐号名称已经存在！',0,'unique',1],
        ['password',"6,18",'密码长度不正确!',0,'length'],
        ['repassword','password','确认密码不正确!',0,'confirm'],
        ['mobile','require','手机号必须！'],
        ['mobile','11',"手机号不正确!",0,'length'],
        ['mobile','','手机号已经存在！',0,'unique',1],
        ['email','require','邮箱必须！'],
        ['email','email',"邮箱格式不正确",0,'email'],
        ['email','','邮箱已经存在！',0,'unique',1]
    ];
}