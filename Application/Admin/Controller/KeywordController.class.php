<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/24/16
 * Time: 8:52 AM
 */

namespace Admin\Controller;

use Common\Controller\CommonController;
use Common\Tools;

class KeywordController extends AuthController
{
    /**
     * 关键词列表
     */
    public function keyword_list(){
        $sql = "SELECT * FROM destoon_appknow_keyword";

        $data = M('')->query($sql);

        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 关键词删除
     */
    public function keyword_delete(){
        $id = I('get.id');
        if (!empty($id)){
            $result = D('keyword')->delete($id);
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
     * 关键词添加
     */
    public function keyword_add(){
        $opt = I('get.action');
        if($opt == 'add'){
            if($this->findKeyword(I('get.keyword')) == 0){
                $data = array();
                $data['keyword'] = I('get.keyword');
                $data['stars'] = I('get.stars');
                $data['addtime'] = time();

                $result = D('keyword')->add($data);
                if($result){
                    $this->ajaxReturn(1);
                }else{
                    $this->ajaxReturn(0);
                }
            }else{
                $this->ajaxReturn(2);
            }
        }
        $this->display();
    }

    /**
     * 获取是否已经添加过
     */
    public function findKeyword($keyword){
        $resutl = D('keyword')->where(array('keyword'=>$keyword))->count();
        return (int)$resutl;
    }
}