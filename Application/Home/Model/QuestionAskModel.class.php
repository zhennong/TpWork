<?php
/**
 * User: 1596229276@qq.com
 * Date: 2016-09-05
 * Time: 下午08:21
 */

namespace Home\Model;
use Common\Tools;
use Think\Model;
use Home\Api\ApiAppKnow;

class QuestionAskModel extends Model{
    protected $tableName = 'question_ask';

    /**
     * 通过用户ID获取提问列表 （2016.09.05） 新接口
     * @param $uid
     */
    public function getUidByAskList($uid){
        $api = new ApiAppKnow();
        $data = $this->alias('a')
                     ->join('destoon_appknow_member_profile AS b ON b.userid = a.uid')
                     ->where(array('a.uid' => $uid))
                     ->field('a.id,a.uid,a.content,a.addtime,a.thumb0,a.thumb1,a.thumb2,a.thumb3,a.thumb4,a.thumb5,a.catid,a.score,a.answer_number,b.truename,b.nickname,b.avatar,b.areaid,b.is_ok,b.memberships')
                     ->order('addtime desc')
                     ->select();
        foreach ($data AS $k=>$v){
            $data[$k]['addtime'] = $api->format_date($v['addtime']);
            $data[$k]['address'] = implode(' ',$api->getAreaFullNameFromAreaID($v['areaid']));
            if($v['avatar'] == null){
                $data[$k]['avatar'] = false;
            }
            for ($i = 0; $i < 6; $i++) {
                if ($v['thumb' . $i]) {
                    $data[$k]['ask_images'][] = $v['thumb' . $i];
                    $data[$k]['image_count'] = $i + 1;
                }
            }
        }
        return $data;
    }

    /**
     * 通过问题ID获取提问列表 （2016.09.05） 新接口
     * @param $askid
     * @return mixed
     */
    public function getIdByAskList($askid){
        $api = new ApiAppKnow();
        $data = $this->alias('a')
                     ->join('LEFT JOIN destoon_appknow_member_profile AS b ON b.userid = a.uid')
                     ->where(array('a.id' => $askid))
                     ->field('a.id,a.uid,a.content,a.addtime,a.thumb0,a.thumb1,a.thumb2,a.thumb3,a.thumb4,a.thumb5,a.catid,a.score,a.answer_number,b.truename,b.nickname,b.avatar,b.areaid,b.is_ok,b.memberships')
                     ->order('addtime desc')
                     ->select();
        foreach ($data AS $k=>$v){
            $data[$k]['addtime'] = date('Y-m-d',$v['addtime']);
            $data[$k]['address'] = implode(' ',$api->getAreaFullNameFromAreaID($v['areaid']));
            for ($i = 0;$i < 6;$i ++){
                if ($v['thumb' . $i]) {
                    $data[$k]['ask_images'][] = $v['thumb' . $i];
                    $data[$k]['image_count'] = $i + 1;
                }
            }
        }
        return $data[0];
    }

    /**
     * 通过用户ID获取回答列表 （2016.09.05） 新接口
     * @param $uid
     */
    public function getUidByAnswerList($uid){
        $data = D('QuestionAnswer')
                ->field('askid')
                ->where(array('uid'=>$uid))
                ->order('addtime desc')
                ->select();
        foreach ($data as $k=>$v){
            $askid[] = $v['askid'];
        }
        $askid = array_unique($askid);
        foreach ($askid as $k=>$v){
            $data = $this->getIdByAskList($v);
            if(!empty($data)){
                $list[] = $data;
            }
        }

        !empty($list) ? $list : array();
        return $list;
    }
}