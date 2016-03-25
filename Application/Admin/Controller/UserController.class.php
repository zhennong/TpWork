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
                    M('AuthGroupAccess')->where(['uid'=>$id])->delete();
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
                $this->display("User/group_distribution");
                break;

            case 'edit_user_group_list': //编辑用户组
                $AuthGroupAccess = M('AuthGroupAccess');
                $AuthGroupAccess->where(['uid' => I('post.user_id')])->delete();
                foreach (I("post.user_group_list") as $k => $v) {
                    $datas[] = ['uid' => I('post.user_id'), 'group_id' => $v];
                }
                if(count($datas)==0){
                    echo 1;
                }else{
                    if ($AuthGroupAccess->addAll($datas)) {
                        echo 1;
                    }
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
        switch($_POST['operate']){
            case 'add_group':
                $AuthGroup = D('AuthGroup');
                $x = $AuthGroup->create();
                if($x){
                    $y = $AuthGroup->data($x)->add();
                    if($y){
                        echo 1;
                    }
                }else{
                    echo $AuthGroup->getError();
                }
                break;

            case 'edit_group':
                $AuthGroup = D('AuthGroup');
                $x = $AuthGroup->create();
                if($x){
                    $y = $AuthGroup->data($x)->save();
                    if($y){
                        echo 1;
                    }
                }else{
                    echo $AuthGroup->getError();
                }
                break;

            case 'delete_group':
                $Group = M('AuthGroup');
                $id = I("post.group_id");
                M('AuthGroupAccess')->where(['group_id'=>$id])->delete();
                if ($Group->delete($id)) {
                    echo 1;
                }else{
                    echo 0;
                }
                break;

            default:
                $group_list = $this->getAuthGroupDetail();
                $this->assign(['group_list' => $group_list]);
                $this->display();
                break;
        }
    }

    /**
     * 节点管理
     */
    public function node_manage()
    {
        switch($_POST['operate']){
            case 'add_node': //添加节点
                $AuthRule = D('AuthRule');
                $x = $AuthRule->create();
                if($x){
                    if($AuthRule->data($x)->add()){
                        echo 1;
                    }
                }else{
                    echo $AuthRule->getError();
                }
                break;

            case 'edit_node': //编辑节点
                $AuthRule = D('AuthRule');
                $x = $AuthRule->create();
                if($x){
                    if($AuthRule->data($x)->save()){
                        echo 1;
                    }
                }else{
                    echo $AuthRule->getError();
                }
                break;

            case 'delete_node': //删除节点
                $node_id = I("post.node_id");
                if(M('AuthRule')->delete($node_id)){
                    echo 1;
                }else{
                    echo 0;
                }
                break;

            default:
                $node_tree = Tools::list2tree($this->authAll);
                foreach($this->authAll as $k => $v){
                    if($v['pid']==0){
                        $p_node_list[] = $v;
                    }
                }
                $this->assign(['node_tree' => $node_tree,'p_node_list'=>$p_node_list]);
                $this->display();
                break;
        }
    }

    /**
     * 权限管理
     */
    public function auth_manage(){
        switch($_POST['operate']){

            case 'change_group':
                $rules_select = M('AuthGroup')->where(['id'=>I("post.group_id")])->field('rules')->select();
                $rules = Tools::str2arr($rules_select[0]['rules'],',');
                echo json_encode($rules);
                break;

            case 'edit_auth':
                $group_id = I("post.group_id");
                $auth = I("post.auth");
                $data = ['rules'=>Tools::arr2str($auth)];
                M('AuthGroup')->where(['id'=>$group_id])->save($data);
                break;

            default:
                $groups = M('AuthGroup')->field(['id','title'])->select();
                $rules = M('AuthRule')->field(['id','pid','title'])->select();
                $rules = Tools::list2tree($rules);
                $this->assign(['groups'=>$groups,'rules'=>$rules]);
                $this->display();
                break;
        }
    }
}