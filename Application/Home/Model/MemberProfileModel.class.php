<?php
/**
 * User: 1596229276@qq.com
 * Date: 2016-09-07
 * Time: 下午16:21
 */

namespace Home\Model;
use Common\Tools;
use Think\Model;
use Home\Api\ApiAppKnow;

class MemberProfileModel extends Model{
    protected $tableName = 'member_profile';

    /**
     * 通过用户ID获取会员详细信息
     * @param $uid            用户ID
     * @param null $type      用户类型 （专家）
     * @return mixed
     */
    public function getMemberInfo($uid,$type = null){
        $api = new ApiAppKnow();
        $list = D('MemberProfile')->alias('a')->join('RIGHT JOIN destoon_ucenter_member AS b ON b.userid = a.userid')->where(array('b.userid'=>$uid))->select();
        foreach ($list AS $k=>$v){
            $list[$k]['addtime'] = date('Y-m-d',$v['addtime']);
            $list[$k]['address'] = Tools::arr2str(' ',$api->getAreaFullNameFromAreaID($v['areaid']));

            $list[$k]['memberships'] = $api->pos[$v['memberships']];                  //会员类型
            $list[$k]['adot_count'] = $this->getMemberAdoptCount($uid);                //获取采纳数量
            $list[$k]['agree_count'] = $this->getAnswerAgreeCount($uid);               //获取点赞数量
            $list[$k]['attention_count'] = $this->getMemberAttentionCount($uid);       //获取关注数量

            //判断是否是专家
            if($type == 'expert'){
                $list2 = D('ExpertProfile')->where(array('userid'=>$v['userid']))->select();
                foreach ($list2 AS $k2=>$v2){
                    $list[$k]['expert_details'] = $v2;
                }
                if(count($list2) > 0){
                    $list[$k]['memberships'] = $api->pos[10];
                }
            }
        }
        return $list;
    }

    /**
     * 获取会员采纳数量
     * @param $uid   会员ID
     * @return mixed
     */
    public function getMemberAdoptCount($uid){
        $count = D('QuestionAnswer')->where(array('uid'=>$uid))->count();
        return $count;
    }

    /**
     * 获取会员点赞数量
     * @param $uid   会员ID
     * @return int
     */
    public function getAnswerAgreeCount($uid){
        $data = D('QuestionAnswer')
                ->where(array('uid'=>$uid,'agree_times'=>array('gt',0)))
                ->field('SUM(agree_times) AS count')
                ->find();
        if (empty($data['count'])||$data['count'] == null){
            $data['count'] = 0;
        }
        return $data['count'];
    }

    /**
     * 获取会员关注数量
     * @param $uid  会员ID
     * @return mixed
     */
    public function getMemberAttentionCount($uid){
        $count = D('MemberFans')->where(array('fans_uid'=>$uid))->count();
        return $count;
    }
}