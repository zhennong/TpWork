<?php
namespace Admin\Controller;

use Common\Tools;

class IndexController extends AuthController {
    public $start,$end;
    public function _initialize(){
        parent::_initialize();
        $this->start = strtotime(date('Y-m-d 00:00:00'));
        $this->end = strtotime(date('Y-m-d 23:59:59'));
    }

    public function index(){
        $info = array();
        $info['mem_count'] = $this->getMemberCount();
        $info['mem_t_count'] = $this->getMemberCount(1);

        $info['exp_count'] = $this->getExpertCount();
        $info['exp_t_count'] = $this->getExpertCount(1);

        $info['ask_count'] = $this->getAskCount();
        $info['ask_t_count'] = $this->getAskCount(1);

        $info['fee_count'] = $this->getFeedbackCount();
        $info['fee_t_count'] = $this->getFeedbackCount(1);

        $this->assign(['info'=>$info]);
        $this->display();
    }

    /**
     * 获取用户总数
     * @param int $tag 0 全部  1 当日
     */
    protected function getMemberCount($tag = 0){
        if($tag == 1){
            $count = D('Member')->where("addtime >= $this->start and addtime < $this->end")->count();
        }else{
            $count = D('Member')->count();
        }
        return $count;
    }

    /**
     * 获取用户总数
     * @param int $tag 0 全部  1 当日
     */
    protected function getExpertCount($tag = 0){
        if($tag == 1){
            $map['addtime'] = array(array($this->start),array($this->end));
            $count = D('Expert')->where($map)->count();
        }else{
            $count = D('Expert')->count();
        }
        return $count;
    }

    /**
     * 获取问答总数
     * @param int $tag 0 全部  1 当日
     */
    protected function getAskCount($tag = 0){
        if($tag == 1){
            $map['addtime'] = array(array($this->start),array($this->end));
            $count = D('Ask')->where($map)->count();
        }else{
            $count = D('Ask')->count();
        }
        return $count;
    }

    /**
     * 获取回馈总数
     * @param int $tag 0 全部  1 当日
     */
    protected function getFeedbackCount($tag = 0){
        if($tag == 1){
            $map['addtime'] = array(array($this->start),array($this->end));
            $count = D('FeedBack')->where($map)->count();
        }else{
            $count = D('FeedBack')->count();
        }
        return $count;
    }

    /**
     * 用户详情
     */
    public function userInfo()
    {
        $userInfo = $this->admin_user;
        $this->assign(['userInfo'=>$userInfo]);
        $this->display();
    }
}