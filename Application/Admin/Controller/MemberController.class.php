<?php
namespace Admin\Controller;

class MemberController extends AuthController {

    /**
     * 会员列表
     */
    public function member_list(){
        $sql = "SELECT um.userid,um.mobile,um.addtime,mp.nickname,mp.truename,mp.score,mp.areaid,mp.last_login_time FROM __MALL_ucenter_member AS um LEFT JOIN __MALL_appknow_member_profile AS mp ON um.userid = mp.userid";
        $data = $this->MallDb->list_query($sql);
        foreach($data AS $k=>$v){
            $data[$k] = $v;
            $data[$k]['score'] = $v['score'] != ''?$v['score']:0;
            $data[$k]['addtime'] = date('Y-m-d h:i:s',$v['addtime']);
            $data[$k]['last_login_time'] = date('Y-m-d h:i:s',$v['last_login_time']);
            $area = getAreaFullNameFromAreaID($v['areaid']);
            $data[$k]['area'] = arr2str($area,'');
        }
        $this->assign(['data'=>$data]);
        $this->display();
    }

    /**
     * 会用详情页
     */
    public function member_profile(){

    }

    /**
     * 会员添加/更新
     */
    public function member_manage(){
        $this->display();
    }

    /**
     * 会员删除
     */
    public function member_delete(){
        $this->display();
    }

}