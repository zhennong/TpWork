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
        $Ask = D('Ask');
        /*
         * 查询表信息
         */
        $list = $Ask->query("select a.id,a.content,a.addtime,a.perfect_answer_id,a.areaid,a.catid,b.cat_name,c.nickname from destoon_appknow_question_ask as a left join destoon_appknow_category as b on a.catid = b.id left join destoon_appknow_member_profile as c on c.userid = a.uid GROUP BY a.addtime");

        foreach ($list as $k => $v) {
            $question_list[$k]['id'] = $v['id'];
            $question_list[$k]['content'] = $v['content'];
            $question_list[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
            $question_list[$k]['perfect_answer_id'] = $v['perfect_answer_id'];
            $question_list[$k]['areaid'] = $v['areaid'];
            $question_list[$k]['cat_name'] = $v['cat_name'];
            $question_list[$k]['nickname'] = $v['nickname'];
            $arealist = getAreaFullNameFromAreaID($v['areaid']);
            $question_list[$k]['area'] = arr2str($arealist, '');

            $count = D()->query("SELECT COUNT(*) AS count FROM destoon_appknow_question_answer WHERE askid =".$v['id']);
            $question_list[$k]['count'] = $count[0]['count'];
        }
        $this->assign(['question_list' => $question_list]);
        $this->display();
    }

    /*
     * 查看提问
     */

    function question_view()
    {
        $id = I('get.id');
        if (!empty($id)) {
            $Ask = D('Ask');
            $map['id'] = $id;
            $sql = $Ask->query("select a.id,a.content,a.addtime,a.perfect_answer_id,a.areaid,a.catid,b.id AS cate_id,b.cat_name,c.nickname from destoon_appknow_question_ask as a left join destoon_appknow_category as b on a.catid = b.id left join destoon_appknow_member_profile as c on c.userid = a.uid WHERE a.id=$id");
            foreach ($sql as $k => $v) {
                $view_list[$k]['id'] = $v['id'];
                $view_list[$k]['content'] = $v['content'];
                $view_list[$k]['addtime'] = date('Y-m-d h:i:s', $v['addtime']);
                $view_list[$k]['perfect_answer_id'] = $v['perfect_answer_id'];
                $view_list[$k]['areaid'] = $v['areaid'];
                $view_list[$k]['cat_name'] = $v['cat_name'];
                $view_list[$k]['nickname'] = $v['nickname'];
                $arealist = getAreaFullNameFromAreaID($v['areaid']);
                $view_list[$k]['area'] = arr2str($arealist, '');
            }
            $this->assign(['view_list' => $view_list]);
        }
        $this->display();
    }

    /*
     *添加问题
     */
    function question_add()
    {
        $opt = I('get.action');
        if ($opt == 'add') {
            $data_list = array();
            $data_list["id"] = I('get.id');
            $data_list["uid"] = I('get.uid');
            $data_list["content"] = I('get.content');
            $data_list["catid"] = I('get.catid');
            $result = D('Ask')->add($data_list);
            if ($result) {
                $this->ajaxReturn(1);
            } else {
                $this->ajaxReturn(0);
            }
        }
        $this->display();
    }

    /*
     * 问题修改
     */

    function question_edit()
    {
        /*
         * 获取更新
         */
        $opt = I('get.action');
        if ($opt == 'edit') {
            $id = I('get.id');
            if (!empty($id)) {
                $data_list = array();
                $data_list["id"] = I('get.id');
                $data_list["uid"] = I('get.uid');
                $data_list["content"] = I('get.content');
                $data_list["addtime"] = strtotime(I('get.addtime'));
                $data_list["catid"] = I('get.catid');
                $map['id'] = $id;
                $result = D('Ask')->where($map)->save($data_list);
                if ($result) {
                    $this->ajaxReturn(1);
                } else {
                    $this->ajaxReturn(0);
                }
            } else {
                $this->ajaxReturn(2); //参数异常
            }
        }

        /*
         * 默认显示
         */
        $id = I('get.id');
        $Ask = D('Ask');
        $map['id'] = $id;
        $sql = $Ask->query("select a.id,a.uid,a.content,a.addtime,a.catid,b.id AS cate_id,b.cat_name,c.nickname from destoon_appknow_question_ask as a left join destoon_appknow_category as b on a.catid = b.id left join destoon_appknow_member_profile as c on c.userid = a.uid WHERE a.id=$id");
        foreach ($sql as $k => $v) {
            $edit_list[$k]['id'] = $v['id'];
            $edit_list[$k]['nickname'] = $v['nickname'];
            $edit_list[$k]['uid'] = $v['uid'];
            $edit_list[$k]['content'] = $v['content'];
            $edit_list[$k]['addtime'] = date('Y-m-d h:i:s', $v['addtime']);
            $edit_list[$k]['cat_name'] = $v['cat_name'];
            $edit_list[$k]['catid'] = $v['catid'];
            $arealist = getAreaFullNameFromAreaID($v['areaid']);
            $edit_list[$k]['area'] = arr2str($arealist, '');
        }

        $sql = "SELECT * FROM destoon_appknow_category WHERE pid = 0";
        $p_data = M()->query($sql);

        $this->assign(['edit_list' => $edit_list,'p_data'=>$p_data]);
        $this->display();
    }

    public function getCategory($cat_id = 0){
        $sql = "SELECT * FROM destoon_appknow_category WHERE pid = ".$cat_id;
        $p_data = M()->query($sql);
        $this->ajaxReturn($p_data);
    }

    /*
     * 删除
     */

    public function question_delete()
    {
        $id = I('get.id');
        if (!empty($id)) {
            $result = D('Ask')->delete($id);
            if ($result) {
                $this->ajaxReturn(1);
            } else {
                $this->ajaxReturn(0);
            }
        } else {
            $this->ajaxReturn(2); //参数异常
        }
    }

    /**
     * 获取问答回复
     */
    public function question_answer(){
        $id = I('get.id');
        $sql = "select a.id,a.askid,a.content,a.addtime,b.content as ask_content,c.mobile from destoon_appknow_question_answer as a LEFT JOIN destoon_appknow_question_ask as b on a.askid = b.id LEFT JOIN destoon_ucenter_member as c on c.userid = a.uid WHERE a.askid = $id";
        $data = M('')->query($sql);
        foreach ($data as $k=>$v){
            $data[$k]['addtime'] = date("Y-m-d H:i:s",$v['addtime']);
        }
        $this->assign(['data'=>$data]);
        $this->display();
    }

    //问答添加
    public function question_answer_add(){
        $id = I('get.id');
        if(!empty($id)){
            $this->assign(['id'=>$id]);
        }
        $opt = I('get.action');
        if ($opt == 'add') {
            $data_list = array();
            $data_list["askid"] = I('get.id');
            $data_list["content"] = I('get.content');
            $data_list["uid"] = I('get.userid');
            $data_list["addtime"] = time();
            $result = D('question_answer')->add($data_list);
            if ($result) {
                $this->ajaxReturn(1);
            } else {
                $this->ajaxReturn(0);
            }
        }

        $this->display();
    }

    /**
     * 回答删除
     */
    public function question_answer_delete(){
        $id = I('get.id');
        if (!empty($id)) {
            $result = D('question_answer')->delete($id);
            if ($result) {
                $this->ajaxReturn(1);
            } else {
                $this->ajaxReturn(0);
            }
        } else {
            $this->ajaxReturn(2); //参数异常
        }
    }

}