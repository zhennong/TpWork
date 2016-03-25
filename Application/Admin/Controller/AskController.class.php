<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-3-24
 * Time: 下午2:43
 */

namespace Admin\Controller;


class AskController extends AuthController
{
    public function question()
    {
        /*
        * 获取提问列表信息
        */
        $Dao = D('Ask');
        /*
         * 查询表信息
         */
        $list = $Dao->query("select a.id,a.content,a.addtime,a.perfect_answer_id,a.areaid,a.catid,b.cat_name,c.nickname from destoon_appknow_question_ask as a left join destoon_appknow_category as b on a.catid = b.id left join destoon_appknow_member_profile as c on c.userid = a.uid");
        foreach ($list as $k => $v) {
            $question_list[$k]['id'] = $v['id'];
            $question_list[$k]['content'] = $v['content'];
            $question_list[$k]['addtime'] = date('Y-m-d h:i:s', $v['addtime']);
            $question_list[$k]['perfect_answer_id'] = $v['perfect_answer_id'];
            $question_list[$k]['areaid'] = $v['areaid'];
            $question_list[$k]['cat_name'] = $v['cat_name'];
            $question_list[$k]['nickname'] = $v['nickname'];
            $arealist = getAreaFullNameFromAreaID($v['areaid']);
            $question_list[$k]['area'] = arr2str($arealist,'');
        }
        $this->assign(['question_list' => $question_list]);
        $this->display();
    }
}