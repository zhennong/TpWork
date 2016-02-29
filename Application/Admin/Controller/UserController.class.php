<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/24/16
 * Time: 12:01 PM
 */

namespace Admin\Controller;


use Common\Tools;

class UserController extends AuthController
{
    public function user_index()
    {
        $this->display('Index:index');
    }

    /**
     * 用户管理
     */
    public function user_manage()
    {
        switch ($_POST['operate']) {
            case 'add_admin_user':
                $AdminUser = D('AdminUser');
                $x = $AdminUser->create();
                if ($x['account'] && $x['mobile'] && $x['email']) {
                    if ($x['id'] > 0) {
                        $y = $AdminUser->save();
                    } else {
                        $y = $AdminUser->add();
                    }
                    echo $y;
                } else {
                    echo $AdminUser->getError();
                }
                break;

            case 'edit_admin_user':
                $AdminUser = D('AdminUser');
                $x = $AdminUser->create();
                if ($x['account'] && $x['mobile'] && $x['email']) {
                    if ($x['id'] > 0) {
                        $y = $AdminUser->save();
                    } else {
                        $y = $AdminUser->add();
                    }
                    echo $y;
                } else {
                    echo $AdminUser->getError();
                }
                break;

            case 'delete_admin_user':
                $AdminUser = D('AdminUser');
                if ($id = I("post.delete_id")) { //  删除用户
                    if ($AdminUser->delete($id)) {
                        echo 1;
                    }
                }
                break;

            case 'display_user_group_list': // 展示用户组
                $user_id = I("post.user_id");
                $group_all = M('AuthGroup')->select();
                $group_sel = M('AuthGroupAccess')->where(['uid'=>$user_id])->select();
                foreach($group_sel as $k => $v){
                    $x[] = $v['group_id'];
                }
                foreach($group_all as $k => $v){
                    $user_group_list[$k] = $v;
                    if(in_array($v['id'],$x)){
                        $user_group_list[$k]['group_checked'] = "checked='checked'";
                    }else{
                        $user_group_list[$k]['group_checked'] = null;
                    }
                }
                $this->assign(['user_id' => $user_id, 'user_group_list' => $user_group_list]);
//                Tools::_vp($user_group_list);
                $this->display("User/group_distribution");
                break;

            case 'edit_user_group_list': //编辑用户组
                $AuthGroupAccess = M('AuthGroupAccess');
                $AuthGroupAccess->where(['uid' => I('post.user_id')])->delete();
                foreach (I("post.user_group_list") as $k => $v) {
                    $datas[] = ['uid' => I('post.user_id'), 'group_id' => $v];
                }
                if ($AuthGroupAccess->addAll($datas)) {
                    echo 1;
                }
                break;

            default:
                $user_list = $this->getAdminUserDetail(null, ['group_detail']);
                $this->assign(['user_list' => $user_list]);
                $this->display();
                break;

        }
    }

    /**
     * 用户组管理
     */
    public function group_manage()
    {
        $group_list = $this->getAuthGroupDetail();
        $this->assign(['group_list' => $group_list]);
        $this->display();
    }

    /**
     * 节点管理
     */
    public function node_manage()
    {
        $node_tree = Tools::list2tree($this->authAll);
        $this->assign(['node_tree' => $node_tree]);
        $this->display();
    }
}