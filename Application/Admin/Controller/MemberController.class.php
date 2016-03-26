<?php
namespace Admin\Controller;

use Common\Tools;

class MemberController extends AuthController {

    /**
     * 会员列表
     */
    public function member_list(){
//        $sql = "SELECT um.userid,um.mobile,um.addtime,mp.nickname,mp.truename,mp.score,mp.areaid,mp.last_login_time FROM __MALL_ucenter_member AS um LEFT JOIN __MALL_appknow_member_profile AS mp ON um.userid = mp.userid";
//        $data = $this->MallDb->list_query($sql);
//        foreach($data AS $k=>$v){
//            $data[$k] = $v;
//            $data[$k]['score'] = $v['score'] != ''?$v['score']:0;
//            $data[$k]['addtime'] = date('Y-m-d h:i:s',$v['addtime']);
//            $data[$k]['last_login_time'] = date('Y-m-d h:i:s',$v['last_login_time']);
//            $area = getAreaFullNameFromAreaID($v['areaid']);
//            $data[$k]['area'] = arr2str($area,'');
//        }

        $data = D('Member')->relation(true)->select();

        dump($data);exit;

        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 会用详情页
     */
    public function member_profile(){
        $userid = I('get.userid');
        if(!empty($userid)){
            $sql = "SELECT um.userid,um.mobile,um.addtime,mp.* FROM __MALL_ucenter_member AS um LEFT JOIN __MALL_appknow_member_profile AS mp ON um.userid = mp.userid WHERE um.userid = ".$userid;
            $data = $this->MallDb->list_query($sql);
            foreach($data AS $k=>$v){
                $data[$k] = $v;
                $data[$k]['score'] = $v['score'] != ''?$v['score']:0;
                $data[$k]['addtime'] = date('Y-m-d h:i:s',$v['addtime']);
                $data[$k]['logintimes'] = date('Y-m-d h:i:s',$v['logintimes']);
                $data[$k]['reg_time'] = date('Y-m-d h:i:s',$v['reg_time']);
                $data[$k]['last_login_time'] = date('Y-m-d h:i:s',$v['last_login_time']);
                $area = getAreaFullNameFromAreaID($v['areaid']);
                $data[$k]['area'] = arr2str($area,'');
            }
        }
        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 会员添加/更新
     */
    public function member_manage(){
        $userid = I('get.userid');
        if(!empty($userid)){
            //编辑
        }else{
            //添加



        }
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

    /**
     * 会员删除
     */
    public function member_delete(){
        $userid = I('get.userid');
        $status = D('Member')->where(['userid'=>$userid])->delete();
        if($status){
            M('MemberProfile')->where(['userid'=>$userid])->delete();
            $this->ajaxReturn(1);
        }else{
            $this->ajaxReturn(0);
        }
    }
}
