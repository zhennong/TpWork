<?php


/**
 * 文件描述
 *修正加载性能问题
 * @author      top_iter
 * @date 2016年8月25日 上午9:21:35
 * @version 1.1.0
 * @copyright  Copyright 2016
 */


namespace Admin\Controller;
use  Think\Page;
class AskController extends AuthController
{

    /**
     * 方法注释说明 ...
     * tags  获取提问信息列表，优化性能
     * @param unknowtype
     * @return return_type
     * @author  top_iter@qq.com
     * @date 2016年8月26日下午2:08:48
     * @version v1.0.0
     */
    
    public function question()
    {
      
        $Ask = D('Ask');
         $map=array();
        if($mobile=trim($_POST['mobile']) ){
            if(is_numeric(substr($mobile,0,3))){
			$mid=D('MemberProfile')->field('userid')->where(array('mobile'=>$mobile))->find();
			dump($mid);
			$map['uid']=(int)$mid['userid'];
            $this->assign('mobile',$mobile);
            }else{
                
                $map['content']=array('like','%'.$mobile.'%');
                $this->assign('mobile',$mobile);
                
            }
        }
        
        $count =  $Ask->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $question_list = $Ask->field('id,content,addtime,perfect_answer_id,areaid,catid,uid,answer_number')->where($map)->order(array('addtime' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$askids=$uids=$catids=array();
        foreach ($question_list as $k => $v) {
            $question_list[$k]['id'] = $v['id'];
			$catids[$v['catid']]=$v['catid'];
			$uids[$v['uid']]=(int)$v['uid'];
			$question_list[$k]['uid'] =(int)$v['uid'];
            $question_list[$k]['content'] = $v['content'];
            $question_list[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
            $question_list[$k]['perfect_answer_id'] = $v['perfect_answer_id'];
            $question_list[$k]['areaid'] = $v['areaid'];
            $question_list[$k]['cat_name'] = $v['cat_name'];
            $question_list[$k]['answer_number'] = $v['answer_number'];
        };
        $this->assign(['question_list' => $question_list]); 
		$catlist=D('Category')->cache('cate')->itemsByIds($catids);
		$profilelist=D('MemberProfile')->cache('profile')->itemsByIds($uids);
	    $this->assign("catlist",$catlist);
	    $this->assign("profilelist",$profilelist);
	    $this->assign("page",$show);
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
            
            //top_iter新增
            if ($result) {
                //问题统计数相加1
                D('QuestionAsk')->where(array("id"=>$id))->setInc('answer_number',1);
                
                
                
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


