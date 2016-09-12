<?php
/**
 * User: 1596229276@qq.com
 * Date: 2016-09-06
 * Time: 下午13:42
 */

namespace Home\Model;
use Common\Tools;
use Think\Model;
use Home\Api\ApiAppKnow;

class MsgModel extends Model{
    protected $tableName = 'message_reply';

    /**
     * 获取消息列表
     * @param $info ($info['userid'] 用户ID)
     * @return mixed
     */
    public function getMessList($info){
        switch ($info['opt']){
            case 'get_mess_tips': //通知消息
                $data = D('MessageReply')
                        ->field('a.id,a.addtime,a.askid,a.isread,b.content,b.catid,c.mobile,d.truename,d.nickname')
                        ->alias('a')
                        ->join('LEFT JOIN destoon_appknow_question_ask AS b ON b.id = a.askid')
                        ->join('LEFT JOIN destoon_ucenter_member AS c ON c.userid = a.from_uid')
                        ->join('LEFT JOIN destoon_appknow_member_profile AS d ON d.userid = c.userid')
                        ->where(array('a.to_uid'=>$info['userid']))
                        ->order('a.id desc')
                        ->select();
                break;

            case 'get_mess_invite': //邀请消息
                $data = D('MessageInvite')
                        ->field('a.id,a.addtime,a.askid,a.isread,b.content,b.catid,c.mobile,d.truename,d.nickname')
                        ->alias('a')
                        ->join('LEFT JOIN destoon_appknow_question_ask AS b ON b.id = a.askid')
                        ->join('LEFT JOIN destoon_ucenter_member AS c ON c.userid = a.from_uid')
                        ->join('LEFT JOIN destoon_appknow_member_profile AS d ON d.userid = c.userid')
                        ->where(array('a.to_uid'=>$info['userid']))
                        ->order('a.id desc')
                        ->select();
                break;

            case 'get_mess_agree': //点赞消息
                $data = D('MessageAgree')
                        ->field('a.id,a.addtime,a.isread,b.content,b.askid,c.mobile,d.truename,d.nickname')
                        ->alias('a')
                        ->join('LEFT JOIN destoon_appknow_question_answer AS b ON b.id = a.answer_id')
                        ->join('LEFT JOIN destoon_ucenter_member AS c ON c.userid = a.from_uid')
                        ->join('LEFT JOIN destoon_appknow_member_profile AS d ON d.userid = c.userid')
                        ->where(array('a.to_uid'=>$info['userid']))
                        ->order('a.id desc')
                        ->select();
                break;

            case 'get_mess_attention': // 用户关注消息
                $data = D('MessageAttention')
                        ->field('a.id,a.addtime,a.isread,b.mobile,c.truename,c.nickname')
                        ->alias('a')
                        ->join('LEFT JOIN destoon_ucenter_member AS b ON b.userid = a.from_uid')
                        ->join('LEFT JOIN destoon_appknow_member_profile AS c ON c.userid = b.userid')
                        ->where(array('a.to_uid'=>$info['userid']))
                        ->order('a.id desc')
                        ->select();
                break;
            case 'get_mess_sys': //系统消息
                $data = D('MessageSys')
                        ->where("'to_uid' = ".$info['userid']." OR 'to_uid' = 0")
                        ->order('id desc')
                        ->select();
                break;
            default:
                break;
        }
        foreach ($data as $k=>$v){
            $data[$k]['addtime'] = date("Y-m-d",$v['addtime']);
        }
        return $data;
    }

    /**
     * 判断是否已经阅读
     * @param $info ($info['opt'])  操作类型
     * @return int
     */
    public function getMessStatus($info){

        switch ($info['opt']){
            case 'get_mess_tips':      //通知状态
                $result = $this->getMessTable('MessageReply',$info['id']);
                break;
            case 'get_mess_invite':    //邀请状态
                $result = $this->getMessTable('MessageInvite',$info['id']);
                break;
            case 'get_mess_agree':     //点赞状态
                $result = $this->getMessTable('MessageAgree',$info['id']);
                break;
            case 'get_mess_attention': //关注状态
                $result = $this->getMessTable('MessageAttention',$info['id']);
                break;
            case 'get_mess_sys':       //系统消息状态
                $result = $this->getMessTable('MessageSys',$info['id']);
                break;
            default:
                return 101;
                break;
        }
        if($result){
            return 200;
        }else{
            return 220;
        }
    }

    /**
     * 获取消息状态通用方法
     * @param $table        操作数据表
     * @param $id           消息ID
     * @return array|bool
     */
    public function getMessTable($table,$id){
        $data['isread'] = 1;
        $result = D($table)->where(array('id'=>$id))->save($data);
        return $result;
    }
}