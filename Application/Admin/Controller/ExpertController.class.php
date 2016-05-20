<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/24/16
 * Time: 6:03 PM
 */

namespace Admin\Controller;


class ExpertController extends AuthController
{
    /**
     * 专家列表
     */
    public function expert_list(){
        $data = $this->getExpertInfo();
        foreach($data AS $k=>$v){
            $data[$k] = $v;
            $data[$k]['addtime'] = date('Y-m-d h:i:s',$v['addtime']);
        }
        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 获取会员ID
     */
    public function getMemberID(){
        $map['mobile'] = I('get.mobile');	//账号
        $result = D('Member')->field('userid')->where($map)->select();
        if(!empty($result)){
            $uid = $result[0]['userid'];
            $this->ajaxReturn($uid);	//存在
        }else{
            $this->ajaxReturn(0);   //不存在
        }
    }

    /**
     * 专家添加
     */
    public function expert_add(){
        $opt = I('get.action');
        if($opt == 'add'){
            $data = array();
            $data['name'] = I('get.name');
            $data['expert_type'] = I('get.expert_type');
            $data['good_at_crop'] = I('get.good_at_crop');
            $data['good_at_area'] = I('get.good_at_area');
            $data['qq'] = I('get.qq');
            $data['postion'] = I('get.postion');
            $data['company'] = I('get.company');
            $data['content'] = I('get.content');
            $data['addtime'] = time();
            $result = D('Expert')->add($data);
            if($result){
                $this->ajaxReturn(1);
            }else{
                $this->ajaxReturn(0);
            }
        }

        $this->display();
    }

    /**
     * 专家编辑
     */
    public function expert_edit(){
        $opt = I('get.action');
        if($opt == 'edit'){
            $uid = I('get.userid');
            if(!empty($uid)){
                $data = array();
                $data['name'] = I('get.name');
                $data['expert_type'] = I('get.expert_type');
                $data['good_at_crop'] = I('get.good_at_crop');
                $data['good_at_area'] = I('get.good_at_area');
                $data['qq'] = I('get.qq');
                $data['postion'] = I('get.postion');
                $data['company'] = I('get.company');
                $data['content'] = I('get.content');
                $data['status'] = I('get.status');
                $result = D('Expert')->where(['userid'=>$uid])->save($data);
                if($result){
                    $this->ajaxReturn(1);
                }else{
                    $this->ajaxReturn(0);
                }
            }else{
                $this->ajaxReturn(2);
            }
        }
        $userid = I('get.userid');
        $data = $this->getExpertInfo($userid);
        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 专家详情
     */
    public function expert_profile(){
        $userid = I('get.userid');
        if(!empty($userid)){
            $data = $this->getExpertInfo($userid);
            foreach($data AS $k=>$v){
                $id_card_front = $v['id_card_front'];
                if(!is_null($id_card_front)){
                    $data[$k]['id_card_front'] = C('WEB_URL').$id_card_front;
                }else{
                    $data[$k]['id_card_front'] = C('TMPL_PARSE_STRING.__UPLOADS__')."/card_front/nopic.jpg";
                }

                $id_card_back = $v['id_card_back'];
                if(!is_null($id_card_back)){
                    $data[$k]['id_card_back'] = C('WEB_URL').$id_card_back;
                }else{
                    $data[$k]['id_card_back'] = C('TMPL_PARSE_STRING.__UPLOADS__')."/card_front/nopic.jpg";
                }

                $data[$k]['addtime'] = date('Y-m-d h:i:s',$v['addtime']);
            }
            $this->assign(['data'=>$data]);
        }
        $this->display();
    }

    /**
     * 上传专家身份证
     */
    public function expert_upload(){
        $opt = I('post.action');
        if($opt == 'id_card'){
            $uid = I('post.userid');
            if(!empty($uid)){
                $data = array();
                if($_FILES){
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize   =     1024*1024*3 ;// 设置附件上传大小
                    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->rootPath  =      APP_ROOT; // 设置附件上传根目录
                    $upload->savePath  =      C('UPLOADS').'card_front/'; // 设置附件上传（子）目录
                    $upload->autoSub = false;
                    // 上传文件
                    $info   =   $upload->upload();
                    if(!$info) {// 上传错误提示错误信息
                        echo $upload->getError();
                    }else{// 上传成功 获取上传文件信息
                        foreach($info as $k=>$v){
                            $data[$k] = $v['savepath'].$v['savename'];
                        }
                    }
                }

                $map['userid'] = $uid;
                $result = D('Expert')->where($map)->save($data);
                if($result){
                    $status = 1;
                }else{
                    $status = 0;
                }
            }else{
                $status = 2; //参数异常
            }

            $this->assign('status',$status);
        }

        $userid = I('get.userid');
        $data = $this->getExpertInfo($userid);
        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 专家删除
     */
    public function expert_delete(){
        $userid = I('get.userid');
        if(!empty($userid)){
            $result = D('Expert')->delete($userid);
            if($result){
                $this->ajaxReturn(1);
            }else{
                $this->ajaxReturn(0);
            }
        }else{
            $this->ajaxReturn(2); //参数异常
        }
    }

    /**
     * 获取专家信息
     * @param $userid
     * @return mixed
     */
    public function getExpertInfo($userid){
        if(!empty($userid)){
            $data = D('Expert')->where(['userid'=>$userid])->select();
        }else{
            $data = D('Expert')->where(['stauts'=>1])->order('addtime desc')->select();
        }
        return $data;
    }


}