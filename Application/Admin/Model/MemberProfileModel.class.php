<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/26/16
 * Time: 6:54 PM
 */

namespace Admin\Model;


class MemberProfileModel extends AdminModel
{
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
        $id = implode(',',$ids);
        $data = $this->where(array($this->pk=>array('IN',$id)))->select();
        $return = array();
        foreach($data as $val){
            $return[$val[$this->pk]] = $val;
        }
        return $return;
    }	
	
	
	
	
	
	
	
	
}