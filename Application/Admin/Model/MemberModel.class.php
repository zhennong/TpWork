<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/26/16
 * Time: 6:54 PM
 */

namespace Admin\Model;


class MemberModel extends MallRelationModel
{ 
    
    protected $tablePrefix = 'destoon_'; 
    protected $tableName = 'ucenter_member';

    protected $_link = array(
        'MemberProfile'=>array(
            'mapping_type'=>self::HAS_ONE,//HAS_ONE查询出一条
            'class_name'=>'member_profile',
            'mapping_name'=>'member_profile',
            'foreign_key'=>'userid',
        ),
    );
}