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
    
    protected $pk="id";
    protected $tablePrefix ='destoon_appknow_';
    protected $tableName ='category';
    protected $cacheTime = "86400";
    
    
    
    
    protected $_validate = [
        ['cat_index','require','索引必须！'],
        ['cat_name','require','名称必须！'],
        ['cat_index','','索引已经存在！',0,'unique'],
        ['cat_name','','名称已经存在！',0,'unique'],
    ];
    
    
    
    

    /**
     * 根据主键获取数据信息 ...
     * tags
     * @param  int  $id
     * @return array $return
     * @author  top_iter@qq.com
     * @date 2016年8月25日上午9:16:28
     * @version v1.0.0
     */
 
    public function itemsByIds($ids = array()){
        if(empty($ids)) return array();
        $data = $this->where(array($this->pk=>array('IN',$ids)))->select();
        $return = array();
        foreach($data as $val){
            $return[$val[$this->pk]] = $val;
        }
        return $return;
    }
    
    
    
    
    
    
    
    
}