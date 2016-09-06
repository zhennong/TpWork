<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/26/16
 * Time: 6:54 PM
 */

namespace Admin\Model;


class FeedBackModel extends AdminModel
{
    protected $tableName = 'feed_back';

    /**
     *  获取意见反馈数据
     */
    public function getFeedBackData(){
        $data = $this->order('addtime desc')->select();
        foreach ($data AS $k=>$v){
            $profile = D('MemberProfile')->field('truename,nickname')->where(array('userid'=>$v['uid']))->find();
            $data[$k]['name'] = $profile['truename'] != '' ? $profile['truename'] : $profile['nickname'];
            $data[$k]['addtime'] = date('Y-m-d',$v['addtime']);
        }
        return $data;
    }

    /**
     * 删除意见反馈
     * @param $userid
     */
    public function delFeedBackById($id){
        $result = $this->where(array('id'=>$id))->delete();
        return $result;
    }
}