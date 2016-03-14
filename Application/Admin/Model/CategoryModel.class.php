<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/3/16
 * Time: 4:09 PM
 */

namespace Admin\Model;


class CategoryModel extends AdminModel
{
    protected $_validate = [
        ['cat_index','require','索引必须！'],
        ['cat_name','require','名称必须！'],
        ['cat_index','','索引已经存在！',0,'unique'],
        ['cat_name','','名称已经存在！',0,'unique'],
    ];
}