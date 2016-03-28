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
            $this->display();
        }
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
            $data = D('Expert')->where(['stauts'=>1])->select();
        }
        return $data;
    }


}