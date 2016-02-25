<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 10:46 AM
 */

namespace Admin\Controller;



use Common\Controller\CommonController;
use Common\Tools;

abstract class AdminController extends CommonController
{
    public $admin_user = [];//后台用户

    public function _initialize(){
        parent::_initialize();
        // 保存session用户信息到公共成员变量
        $admin_user = session('admin_user');
        if($admin_user){
            $this->admin_user = $admin_user;
        }
        $this->assign(['admin_user'=>$admin_user]);
    }

    /**
     * 获取后台用户详细信息
     * @param $user_id
     * @param array $bind | $bind = ['group_detail','rules_detail']
     * @return [[]]
     */
    public function getAdminUserDetail($map,$bind=[]){
        $x = M('Admin')->where(get_map($map))->select();
        foreach($x as $k => $v){
            $groups = M('AuthGroupAccess')->where(['uid'=>$v['id']])->select();
            foreach($groups as $k1 => $v1){
                $y[] = $v1['group_id'];
            }
            if(in_array('group_detail',$bind)){
                $x[$k]['group_detail'] = $this->getAuthGroupDetail($y);
            }elseif(in_array('rules_detail',$bind)){
                $x[$k]['group_detail'] = $this->getAuthGroupDetail($y,['rules_detail']);
            }
        }
        return $x;
    }

    /**
     * 获取后台用组详细信息
     * @param $group_id
     * @param array $bind | $bind = ['rules_detail']
     * @return [[]]
     */
    public function getAuthGroupDetail($map,$bind=[]){
        $x = M('AuthGroup')->where(get_map($map))->select();
        foreach($x as $k => $v){
            switch($v['status']){
                case 0:
                    $status_name = "禁用中";
                    break;

                case 1:
                    $status_name = "启用中";
                    break;

                default:
                    $status_name = "未定义状态：".$v['status'];
                    break;
            }
            $x[$k]['status_name'] = $status_name;
            if(in_array('rules_detail',$bind)){
                $rules_array = Tools::str2arr($v['rules']);
                foreach($rules_array as $k1 => $v1){
                    $y[$k1]['rule_info'] = M('AuthRule')->where(['id'=>$v1])->find();
                }
                $x[$k]['rules_detail'] = $y;
            }
        }
        return $x;
    }
}