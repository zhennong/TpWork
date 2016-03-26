<?php
namespace Admin\Controller;

use Common\Tools;

class MemberController extends AuthController {

    /**
     * 会员列表
     */
    public function member_list(){
        $data = $this->getMemberInfo();
        $this->assign(['data'=>$data]);
        $this->display();
    }

    //获取用户详细信息
    protected function getMemberInfo($userid){
        if(!empty($userid)){
            $map['userid'] = $userid;
            $data = D('Member')->relation(true)->where($map)->select();
        }else{
            $data = D('Member')->relation(true)->select();
        }
        foreach($data AS $k=>$v){
            $data[$k] = $v;
            $avatar = $v['member_profile']['avatar'];
            if(!is_null($avatar)){
                $data[$k]['avatar'] = __ROOT__.$avatar;
            }else{
                $data[$k]['avatar'] = C('TMPL_PARSE_STRING.__ADMIN__')."images/thumb.jpg";
            }
            $data[$k]['score'] = $v['member_profile']['score'] != ''?$v['member_profile']['score']:0;
            $data[$k]['addtime'] = date('Y-m-d h:i:s',$v['addtime']);
            $data[$k]['last_login_time'] = date('Y-m-d h:i:s',$v['last_login_time']);
            $area = getAreaFullNameFromAreaID($v['member_profile']['areaid']);
            $data[$k]['area'] = arr2str($area,'');
        }
        return $data;
    }

    //检查账号是否已注册
    public function check_account(){
        $map['mobile'] = I('get.mobile');	//账号
        $result = D('Member')->field('userid')->where($map)->count();
        if($result > 0){
            $this->ajaxReturn(1);	//存在
        }else{
            $this->ajaxReturn(0);   //不存在
        }
    }

    /**
     * 会员添加
     */
    public function member_add(){
        $opt = I('get.action');
        if($opt == 'add'){
            $data = array();
            $data["mobile"] = I('get.mobile');
            $data["password"] = md5(md5(I('get.password')));
            $data["status"] = 1;
            $data["addtime"] = time();
            $data["updatetime"] = time();
            $data["appid"] = 2;
            $data["last_login_time"] = time();
            $data["member_profile"] = array(
                "nickname" => I('get.nickname'),
                "sex" => I('get.sex'),
                "qq" => I('get.qq'),
                "truename" => 1,
                "areaid" => I('get.areaid'),
                "address" => 1,
            );
            $result = D('Member')->relation(true)->add($data);
            if($result){
                $this->ajaxReturn(1);
            }else{
                $this->ajaxReturn(0);
            }
        }
        $this->display();
    }

    /**
     * 会员更新
     */
    public function member_edit(){
        $opt = I('get.action');
        if($opt == 'edit'){
            $uid = I('get.userid');

            if(!empty($uid)){
                $data = array();
                $data["updatetime"] = time();
                $data["member_profile"] = array(
                    "nickname" => I('get.nickname'),
                    "sex" => I('get.sex'),
                    "qq" => I('get.qq'),
                    "truename" => I('get.truename'),
                    "areaid" => I('get.areaid'),
                    "address" => I('get.address'),
                );
                $map['userid'] = $uid;
                $result = D('Member')->relation(true)->where($map)->save($data);
                if($result){
                    $this->ajaxReturn(1);
                }else{
                    $this->ajaxReturn(0);
                }
            }else{
                $this->ajaxReturn(2); //参数异常
            }
        }

        $userid = I('get.userid');
        $data = $this->getMemberInfo($userid);
        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 修改密码
     */
    public function member_edit_pwd(){
        $opt = I('get.action');
        if($opt == 'edit_pwd'){
            $uid = I('get.userid');
            if(!empty($uid)){
                $data = array();
                $data["password"] = md5(md5(I('get.password')));
                $map['userid'] = $uid;
                $result = D('Member')->relation(true)->where($map)->save($data);
                if($result){
                    $this->ajaxReturn(1);
                }else{
                    $this->ajaxReturn(0);
                }
            }else{
                $this->ajaxReturn(2); //参数异常
            }
        }

        $userid = I('get.userid');
        $data = $this->getMemberInfo($userid);
        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 会员删除
     */
    public function member_delete(){
        $userid = I('get.userid');
        if(!empty($userid)){
            $result = D('Member')->relation(true)->delete($userid);
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
     * 会用详情页
     */
    public function member_profile(){
        $userid = I('get.userid');
        if(!empty($userid)){
            $map['userid'] = $userid;
            $data = D('Member')->relation(true)->where($map)->select();
            foreach($data AS $k=>$v){
                $data[$k] = $v;
                $data[$k]['score'] = $v['score'] != ''?$v['score']:0;
                $data[$k]['addtime'] = date('Y-m-d h:i:s',$v['addtime']);
                $data[$k]['logintimes'] = date('Y-m-d h:i:s',$v['logintimes']);
                $data[$k]['reg_time'] = date('Y-m-d h:i:s',$v['reg_time']);
                $data[$k]['last_login_time'] = date('Y-m-d h:i:s',$v['last_login_time']);
                $area = getAreaFullNameFromAreaID($v['member_profile']['areaid']);
                $data[$k]['area'] = arr2str($area,'');
            }
        }
        $this->assign(['data'=>$data]);
        $this->display();
    }

    //获取所在地区
    public function getAreas(){
        $areaList = D('Area')->select();
        $area = Tools::get_list_parents($areaList, I('get.areaid'), 'areaid', 'parentid');
        $show['parent_areas'] = $area;
        $this->ajaxReturn($show);
    }

    //获取省市县
    public function get_area_info(){
        $pid = I('get.pid');
        $areaList = D('Area')->where(['parentid'=>$pid])->select();
        $show['area_info'] = $areaList;
        $this->ajaxReturn($show);
    }
}
