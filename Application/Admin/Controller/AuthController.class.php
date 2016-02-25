<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 6:06 PM
 */

namespace Admin\Controller;


use Common\Tools;
use Think\Auth;

class AuthController extends AdminController
{
    public $not_check = [
        'Admin/Index/index'
    ];// 不需要验证的方法
    public $authNames =[]; // 允许访问节点名数组
    public $authList = [[]]; // 允许访问节点列表
    public $authTree = [[]]; // 允许访问节点树

    public function _initialize(){
        parent::_initialize();

        //session不存在时，不允许直接访问
        if(!$this->admin_user['id']){
            Tools::set_redirect_url(Tools::get_url());
            $this->error("尚未登录",U("Admin/Public/login"));
        }

        //session存在时，不需要验证的权限
//        if(!in_array($this->route,$this->not_check)){
//            //下面代码动态判断权限
//            $Auth = new Auth();
//            if (!$Auth->check($this->route, $this->admin_user['id']) && $this->admin_user['id'] != C('ADMIN_USER_ID')) {
//                $this->error('没有权限',U('Admin/Index/index'));
//            }
//        }


        //获取允许访问的节点
        $this->authList = $this->getAuthList($this->admin_user['id']);
        foreach($this->authList as $k => $v){
            $this->authNames[] = $v['name'];
        }
        if(!in_array($this->route,$this->authNames)){
            $this->error('没有权限',U('Admin/Index/index'));
        }
        $this->authTree = Tools::list2tree($this->authList);
        $this->assign(['authNames'=>$this->authNames,'authList'=>$this->authList,'authTree'=>$this->authTree]);
//        Tools::_vp($this->authTree,0,2);
    }

    /**
     * 获取允许访问的节点列
     * @param $user_id
     * @return mixed
     */
    protected function getAuthList($user_id){
        $Auth = new Auth();
        $rules = M('AuthRule')->order("id ASC")->select();
        foreach($rules as $k => $v){
            $a = $this->admin_user['id'] == C('ADMIN_USER_ID');
            $b = in_array($v['name'],$this->not_check);
            $c = $Auth->check($v['name'], $user_id);
            $x = $a || $b || $c;
            if(!$x){
                unset($rules[$k]);
            }
        }
        return $rules;
    }
}