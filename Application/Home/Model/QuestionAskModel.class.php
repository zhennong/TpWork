<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-3-24
 * Time: 下午2:51
 */

namespace Home\Model;
use Common\Tools;
use Think\Model;

class QuestionAskModel extends Model{
    protected $tableName = 'question_ask';

    /**
     * 通过用户ID获取提问列表 （2016.09.05） 新接口
     * @param $uid
     */
    public function getUidByAskList($uid){
        $data = $this->alias('a')->join('destoon_appknow_member_profile AS b ON b.userid = a.uid')->where(array('a.uid' => $uid))->field('a.id,a.uid,a.content,a.addtime,a.thumb0,a.thumb1,a.thumb2,a.thumb3,a.thumb4,a.thumb5,a.catid,a.score,a.answer_number,b.truename,b.nickname,b.avatar,b.areaid,b.is_ok,b.memberships')->order('addtime desc')->select();
        return $data;
    }

    /**
     * 通过问题ID获取提问列表 （2016.09.05） 新接口
     * @param $askid
     * @return mixed
     */
    public function getIdByAskList($askid){
        $data = $this->alias('a')->join('destoon_appknow_member_profile AS b ON b.userid = a.uid')->where(array('a.id' => $askid))->field('a.id,a.uid,a.content,a.addtime,a.thumb0,a.thumb1,a.thumb2,a.thumb3,a.thumb4,a.thumb5,a.catid,a.score,a.answer_number,b.truename,b.nickname,b.avatar,b.areaid,b.is_ok,b.memberships')->order('addtime desc')->select();
        return $data;
    }

    /**
     * 通过用户ID获取回答列表 （2016.09.05） 新接口
     * @param $uid
     */
    public function getUidByAnswerList($uid){
        //$data = $this->alias('a')->join('destoon_appknow_question_answer AS b ON b.askid = a.id')->join('destoon_appknow_member_profile AS c ON c.userid = a.uid')->where(array('b.uid' => $uid))->field('a.id,a.uid,a.content,a.addtime,a.thumb0,a.thumb1,a.thumb2,a.thumb3,a.thumb4,a.thumb5,a.catid,a.score,a.answer_number,c.truename,c.nickname,c.avatar,c.areaid,c.is_ok,c.memberships')->order('addtime desc')->select();
        $data = D('QuestionAnswer')->field('askid')->where(array('uid'=>$uid))->order('addtime desc')->select();
        foreach ($data as $k=>$v){
            $askid[] = $v['askid'];
        }
        $askid = array_unique($askid);

        foreach ($askid as $k=>$v){
            $list[] = $this->getIdByAskList($v);
        }
        return $list;
    }



}