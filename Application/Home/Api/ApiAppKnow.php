<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiAppKnow
 *
 * @author wodrow
 */

namespace Home\Api;

use Common\Tools;

class ApiAppKnow extends Api
{
    public $appid = 2;

    public $favourite_type = array(
        'ask' => 1,
        'article' => 2,
    );

    /*
     * 专家认证状态
     */
    public $expert_status = array(
        'in_review'=>0,//审核中
        'review_ok'=>1,//审核成功
        'review_fail'=>2,//审核失败
    );

    //积分设置（分值）
    public $score_arr = array(
        'sa_login' => 1,        //用户登录
        'sa_questions' => 3,    //用户提问问题
        'sa_answer' => 5,       //用户回答问题
    );

    public $tablePrefix;

    public function __construct()
    {
        parent::__construct();
        $this->tablePrefix = C('DATABASE_MALL_TABLE_PREFIX');
    }


    /*
     * 获取用户详细
     */
    public function getUserDetail($uid,$bind=array()){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member WHERE userid = {$uid}";
        $x = $this->list_query($sql);
        if(in_array('expert_profile',$bind)){
            $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_expert_profile WHERE userid = {$uid} AND status = 1";
            $a = $this->list_query($sql);
        }
        if(in_array('member_profile',$bind)){
            $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile WHERE userid = {$uid}";
            $b = $this->list_query($sql);
            if($b){
                foreach($b as $k => $v){
                    if($v[nickname]==''){
                        $y = $this->list_query("SELECT mobile FROM ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member WHERE userid = {$uid}");
                        $b[$k][nickname] = $y[0][mobile];
                    }
                    $z = $this->getAreaFullNameFromAreaID($v['areaid']);
                    $b[$k]['area_name'] = Tools::arr2str($z,' ');
                }
            }
        }
        foreach($x as $k => $v){
            if(count($a)>0){
                $x[$k]['member_type'] = 'expert';
                $x[$k]['expert_profile'] = $a[0];
            }else{
                $x[$k]['member_type'] = 'landlord';
            }
            if(count($b)>0){
                $x[$k]['member_profile'] = $b[0];
                //$x[$k]['money'] = number_format(intval($b[0]['score'])/10000,2); //用户积分转换 1:10000
                $x[$k]['money'] = 0.00;

                $x[$k]['grade'] = $this->setMemberGrade(intval($b[0]['score']));
            }else{
                $x[$k]['member_profile'] = ['area_name'=>'暂无','nickname'=>$this->mobileHide($v['mobile']),'avatar'=>'image/defaultx20.jpg'];
            }
        }
        return $x;
    }

    /**
     * appcan 上传图片
     */
    public static function uploadImage($_FSN, $upload_dir = '', $name = '')
    {
        if ($upload_dir == '') {
            $upload_dir = APP_ROOT.C('UPLOADS');
        }
        if ($name == '') {
            $name = time();
        }
        $model = new UploadImage($_FSN, $upload_dir, $name);
        $model->imageStart();
        if ($model->imageStauts == 1) {
            $status = 200;
        } else {
            $status = 101;
        }
        return $status;
    }

    /**
     * 根据userid获取用户详细信息
     */
    public function getUserDetialByID($userid)
    {
        # code...
    }

    /**
     * 根据用户手机获取用户信息
     */
    public function getUserFromMobile($mobile)
    {
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member WHERE mobile = '{$mobile}'";
        return $this->list_query($sql);
    }

    /**
     * 获取上次访问时间
     */
    public function getLastLoginTime($userid){
        $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member SET last_login_time = {$this->now} WHERE userid = {$userid}";
        return $this->execute($sql);
    }

    /**
     * 注册
     * @param $info
     * @return int
     */
    public function register($info)
    {
        $password = md5(md5($info[password]));
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member (mobile,password,addtime,updatetime,status,appid,last_login_time) VALUES ('{$info[mobile]}','{$password}',{$this->now},{$this->now},1,{$this->appid},{$this->now})";
        if ($this->execute($sql)) {
            $status = 200;
        } else {
            $status = 203;
        }
        return $status;
    }

    /**
     * 会员积分
     * @param $userid 用户ID
     * @param $module 积分需要添加的模块
     * @return mixed
     */
    public function addScore($userid,$module){
        if($userid){
            $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET score = score + {$this->score_arr[$module]} WHERE userid = {$userid}";
            return $this->execute($sql);
        }
    }

    /**
     * 生成记录信息
     * @param $log_title
     * @param $log_info
     */
    public function putLog($log_info, $log_title='log')
    {
        $log_dir = APP_ROOT.C('UPLOADS')."Log/";
        $log_file_name = "app_know_api_log.md";
        Tools::createDir($log_dir);
        $log_path = $log_dir . "/" . $log_file_name;
        $now = date("Y-m-d H:i:s", time());
        $content = "***\r##" . $now . "\r" . $log_title . ":\r\r```php\r" . var_export($log_info, true) . "\r```\r\r";
        file_put_contents($log_path, $content, FILE_APPEND);
    }

    /**
     * 获取地区信息
     * @param int $pid
     */
    public function getAreaInfo($pid = 0)
    {
        $sql = "SELECT areaid,areaname FROM ".C('DATABASE_MALL_TABLE_PREFIX')."area WHERE parentid = {$pid}";
        return $this->list_query($sql);
    }

    /**
     * 获取地区信息
     * @param int $pid
     */
    public function getAskCategoryInfo($pid = 0)
    {
        $sql = "SELECT id,cat_name FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_category WHERE pid = {$pid}";
        return $this->list_query($sql);
    }

    /**
     * 获取农医问药用户资料
     * @param $userid
     * @return mixed
     */
    public function getAppknowMemberProfile($userid)
    {
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile WHERE userid = {$userid}";
        return $this->list_query($sql);
    }

    /**
     * 设置农医问药用户头像
     * @param $userid
     */
    public function setAppknowMemberAvatar($info)
    {
        if ($this->getAppknowMemberProfile($info[userid])) {
            $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET avatar='{$info[avatar]}' WHERE userid = {$info[userid]}";
        } else {
            $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile (userid,avatar) VALUES ({$info[userid]},'{$info[avatar]}')";
        }
        return $this->execute($sql);
    }

    /**
     * 设置农医问药用户资料
     * @param $userid
     */
    public function setAppknowMemberProfile($info)
    {
        if ($this->getAppknowMemberProfile($info[userid])) {
            $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET nickname='{$info[nickname]}',sex={$info[sex]},qq='{$info[qq]}',truename='{$info[truename]}',areaid={$info[areaid]},address='{$info[address]}',location='{$info[location]}' WHERE userid = {$info[userid]}";
        } else {
            $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile (userid,nickname,sex,qq,truename,areaid,address,location) VALUES ({$info[userid]},'{$info[nickname]}',{$info[sex]},'{$info[qq]}','{$info[truename]}',{$info[areaid]},'{$info[address]}','{$info[location]}')";
        }
        return $this->execute($sql);
    }

    /**
     * 添加快速提问
     * @param $userid
     */
    public function addQuickAsk($info)
    {
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask (uid,content,thumb0,thumb1,thumb2,thumb3,thumb4,thumb5,addtime,catid) VALUES ({$info[uid]},'{$info[content]}','{$info[thumb0]}','{$info[thumb1]}','{$info[thumb2]}','{$info[thumb3]}','{$info[thumb4]}','{$info[thumb5]}',{$this->now},{$info[cat_id]})";
        return $this->execute($sql);
    }

    /**
     * 获取提问列表
     * @param $userid
     */
    public function getAskList($start = null, $limit = null,$cat_id = null)
    {
        $where = "WHERE 1=1";
        if($cat_id != null){
            $where .= " AND catid = {$cat_id}";
        }
        $sql = "SELECT ask.*,profile.nickname,profile.areaid,profile.address,profile.avatar,profile.location,m_member.mobile FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask AS ask
            LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS profile ON ask.uid = profile.userid
            LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member AS m_member ON ask.uid = m_member.userid {$where}
            ORDER BY addtime DESC";
        if ($start != null && $limit != null) {
            $sql = $sql . " LIMIT {$start},{$limit}";
        }
        $x = $this->list_query($sql);
        foreach ($x as $k => $v) {
            if (!$v['nickname']) {
                $x[$k]['nickname'] = $this->mobileHide($v['mobile']);
            }
            $sql = "SELECT id FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer WHERE askid = {$v['id']}";
            $x[$k]['answer_count'] = count($this->list_query($sql));
            $y = $this->getUserDetail($v['uid'],array('expert_profile'));
            $x[$k]['member_type'] = $y[0]['member_type'];
        }
        return $x;
    }

    /**
     * 获取问题详情
     */
    public function getAskInfo($askid)
    {
        $sql = "SELECT ask.*,m_member.mobile,profile.nickname,profile.areaid,profile.address,profile.avatar
            FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask AS ask
            LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member AS m_member ON ask.uid = m_member.userid
            LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS profile ON ask.uid = profile.userid
            WHERE id = {$askid}";
        $x = $this->list_query($sql);
        foreach ($x as $k => $v) {
            if (!$v['nickname']) {
                $x[$k]['nickname'] = $this->mobileHide($v['mobile']);
            }
            $y = $this->getUserDetail($v['uid'],array('expert_profile'));
            $x[$k]['member_type'] = $y[0]['member_type'];
        }
        return $x;
    }

    /**
     * 获取问题解答
     */
    public function getAskAnswers($askid, $start = null, $limit = null)
    {
        $sql = "SELECT answer.*,
            m_member.mobile,profile.nickname,profile.areaid,profile.agreed_times,profile.agreed_times2,profile.avatar
            FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer AS answer
            LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member AS m_member ON answer.uid = m_member.userid
            LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS profile ON answer.uid = profile.userid
            WHERE askid = {$askid} ORDER BY addtime DESC";
        if ($start != null && $limit != null) {
            $sql = $sql . " LIMIT {$start},{$limit}";
        }
        $x = $this->list_query($sql);
        foreach ($x as $k => $v) {
            if (!$v['nickname']) {
                $x[$k]['nickname'] = $this->mobileHide($v['mobile']);
            }
            $y = $this->getUserDetail($v['uid'],array('expert_profile'));
            $x[$k]['member_type'] = $y[0]['member_type'];
        }
        return $x;
    }

    /**
     * 我要回答
     */
    public function addQuestionAnswer($info)
    {
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer (uid,askid,content,addtime) VALUES ({$info[userid]},{$info[askid]},'{$info[content]}',{$this->now})";
        return $this->execute($sql);
    }

    /**
     * 保存回答消息
     */
    public function addMessageReply($info){
        $sql = "INSERT INTO {$this->tablePrefix}appknow_message_reply (from_uid,to_uid,askid,addtime)VALUES({$info['userid']},{$info['to_uid']},{$info['askid']},{$this->now})";
        return $this->execute($sql);
    }


    /**
     * 2016.02.16
     * 意见反馈
     */
    public function addFeedBack($info)
    {
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_feed_back (feed_content,feed_mail,addtime) VALUES ('{$info[feed_content]}','{$info[feed_mail]}',{$this->now})";
        return $this->execute($sql);
    }

    /**
     * 获取专家信息
     * @param $userid
     */
    public function getExpertProfile($userid)
    {
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_expert_profile WHERE userid = {$userid}";
        return $this->list_query($sql);
    }

    /**
     * 2016.02.16
     * 专家认证
     */
    public function addExpertAuthentication($info)
    {
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_expert_profile (userid,name,expert_type,good_at_crop,good_at_area,qq,postion,company,id_card_front,id_card_back,content,addtime) VALUES ({$info[userid]},'{$info[name]}','{$info[expect_type]}','{$info[good_at_crop]}','{$info[good_at_area]}','{$info[qq]}','{$info[postion]}','{$info[company]}','{$info[id_card_front]}','{$info[id_card_back]}','{$info[content]}',{$this->now})";
        return $this->execute($sql);
    }


    /**
     * 获取收藏
     * @param $info
     */
    public function getFavouriteInfo($info)
    {
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_favourite WHERE uid = {$info['userid']} AND obj_id = {$info['obj_id']}";
        $result = $this->execute($sql);
        if ($result > 0) {
            return 217;
        }else{
            return 200;
        }
    }

    /**
     * 添加收藏
     * @param $info
     */
    public function addFavourite($info)
    {
        if (count($this->getFavourite($info[userid], $info[type], $info[obj_id])) > 0) {
            return 217;
            exit();
        }
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_favourite (uid,type,obj_id,addtime) VALUES ({$info['userid']},{$this->favourite_type[$info['type']]},{$info['obj_id']},{$this->now})";
        if ($this->execute($sql)) {
            return 200;
        } else {
            return 215;
        }
    }

    /**
     * 取消添加收藏
     * @param $info
     */
    public function removeFavourite($info){
        $sql = "DELETE FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_favourite WHERE uid = {$info['userid']} AND obj_id = {$info['obj_id']}";

        if ($this->execute($sql)) {
            return 200;
        } else {
            return 219;
        }
    }

    /**
     * 获取当前用户分组收藏
     * @param $uid
     * @param $type
     */
    public function getFavourite($uid, $type = null, $obj_id = null)
    {
        $where = "fav.uid = {$uid}";
        if ($type!=null) {
            $where .= " AND fav.type = {$this->favourite_type[$type]}";
        }
        if ($obj_id!=null) {
            $where .= " AND fav.obj_id = {$obj_id}";
        }
        $sql = "SELECT fav.*,m_profile.nickname,m_profile.avatar FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_favourite AS fav
        LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS m_profile ON fav.uid = m_profile.userid
        WHERE {$where}";
        $x = $this->list_query($sql);
        if ($type == "ask") {
            $table = C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask";
        }
        switch ($type) {
            case 'ask':
                $table = C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask";
                break;
            case 'article':
                break;
            default:
                return null;
                exit();
                break;
        }
        foreach ($x as $k => $v) {
            $sql = "SELECT id,content FROM {$table} WHERE id = {$v[obj_id]}";
            $y = $this->list_query($sql);
            $x[$k]['obj_id'] = $y[0]['id'];
            $x[$k]['obj_content'] = $y[0]['content'];
            $x[$k]['addtime'] = date("Y-m-d H:i", $v['addtime']);
        }
        return $x;
    }

    /**
     * 提问历史
     */
    public function myAskHistory($userid)
    {
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask WHERE uid = {$userid} ORDER BY addtime DESC";
        $x = $this->list_query($sql);
        foreach ($x as $k => $v) {
            $x[$k]['addtime'] = date("Y-m-d H:i", $v['addtime']);
        }
        return $x;
    }

    /**
     * 回答历史
     */
    public function myAnswerHistory($userid)
    {
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer WHERE uid = {$userid} ORDER BY addtime DESC";
        $x = $this->list_query($sql);
        foreach ($x as $k => $v) {
            $x[$k]['addtime'] = date("Y-m-d H:i", $v['addtime']);
        }
        return $x;
    }

    /**
     * 添加关注
     */
    public function addAttention($info)
    {
        if ($info[attention_uid] == $info[fans_uid]) {
            return 221;
            exit();
        }
        if (count($this->getAttention($info[attention_uid], $info[fans_uid])) > 0) {
            return 217;
            exit();
        }

        //关注消息设置
        $this->addMessageAttention($info[fans_uid],$info[attention_uid]);

        //粉丝设置
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans (attention_uid,fans_uid,addtime) VALUES ({$info[attention_uid]},{$info[fans_uid]},{$this->now})";
        $this->execute($sql);
    }

    /**
     * 关注消息设置
     */
    public function addMessageAttention($from_uid,$to_uid){
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_attention (from_uid,to_uid,addtime) VALUES ({$from_uid},{$to_uid},{$this->now})";
        $this->execute($sql);
    }


    /**
     * 获取关注
     */
    public function getAttention($attention_uid = null, $fans_uid = null,$type = null)
    {
        $where = "WHERE id>0 ";
        if($attention_uid!=null){
            $where .= " AND attention_uid = {$attention_uid}";
        }
        if ($fans_uid!=null) {
            $where .= " AND fans_uid = {$fans_uid}";
        }
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans {$where}";
        $x = $this->list_query($sql);
        foreach($x as $k => $v){
            $x[$k]['_id'] = $v[id];
            $fans_detail = $this->getUserDetail($v[fans_uid],array('member_profile','expert_profile'));
            $x[$k]['fans_detail'] = $fans_detail[0];
            $attention_detail = $this->getUserDetail($v[attention_uid],array('member_profile','expert_profile'));
            $x[$k]['attention_detail'] = $attention_detail[0];
            if($x[$k]['attention_detail']['member_type'] != $type){
                unset($x[$k]);
            }
        }
        return $x;
    }

    /**
     * 获取粉丝
     */
    public function getFans($attention_uid = null, $fans_uid = null,$type = null)
    {
        $where = "WHERE id>0 ";
        if($attention_uid!=null){
            $where .= " AND attention_uid = {$attention_uid}";
        }
        if ($fans_uid!=null) {
            $where .= " AND fans_uid = {$fans_uid}";
        }
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans {$where}";
        $x = $this->list_query($sql);
        foreach($x as $k => $v){
            $x[$k]['_id'] = $v[id];
            $fans_detail = $this->getUserDetail($v[fans_uid],array('member_profile','expert_profile'));
            $x[$k]['fans_detail'] = $fans_detail[0];
            $attention_detail = $this->getUserDetail($v[attention_uid],array('member_profile','expert_profile'));
            $x[$k]['attention_detail'] = $attention_detail[0];
            if($x[$k]['fans_detail']['member_type'] != $type){
                unset($x[$k]);
            }
        }
        return $x;
    }

    /**
     * 获取栏目子分类
     * @param $pid
     * @return mixed
     */
    public function getSubCategoryList($pid){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_category WHERE pid = {$pid}";
        $data = $this->list_query($sql);
        return $data;
    }

    /**
     * 获取栏目分类
     * @param null $id
     * @param null $pid
     * @param null $cate_index
     * @param null $cate_name
     * @return mixed
     */
    public function getCategoryList($id = null,$pid = null,$cate_index = null,$cate_name = null,$status = null){
        $where = "WHERE 1 = 1";
        if($id!=null){
            $where .= " AND id = {$id} ";
        }
        if($pid!=null){
            $where .= " AND pid = {$pid} ";
        }
        if($cate_index!=null){
            $where .= " AND cate_index = {$cate_index} ";
        }
        if($cate_name!=null){
            $where .= " AND cat_name = {$cate_name} ";
        }
        if($status!=null){
            $where .= " AND status = {$status} ";
        }
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_category $where";
        $data = $this->list_query($sql);

        return $data;
    }

    /**
     * 查询是否添加过产品分类
     */
    public function getMyCategory($cat_id = null,$userid = null){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_selected_category WHERE cat_id = {$cat_id} AND uid = {$userid}";
        $result = $this->list_query($sql);
        return $result;
    }

    /**
     * 添加个人产品分类
     */
    public function addSelectCategory($info){
        $data = $this->getMyCategory($info[cat_id],$info[userid]);
        if(count($data) > 0){
            return 217;
            exit();
        }
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_selected_category (uid,cat_id) VALUES ({$info[userid]},{$info[cat_id]})";
        if($this->execute($sql)){
            return 200;
        }else{
            return 215;
        }
    }

    /**
     * 获取个人产品分类
     */
    public function getMyCategoryList($userid){
        $sql = "SELECT b.id,b.cat_name,b.cat_img FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_selected_category AS a INNER JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_category AS b ON a.cat_id=b.id WHERE a.uid = {$userid}";
        $data = $this->list_query($sql);
        return $data;
    }

    /**
     * 获取圈子成员数
     */
    public function getCommunityMemberCount($cat_id){
        $sql = "SELECT uid FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_selected_category WHERE cat_id = {$cat_id}";
        $x = $this->list_query($sql);
        return count($x);
    }

    /**
     * getMemberAttention
     */
    public function getMemberAttention($userid){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans WHERE fans_uid = {$userid}";
        $count = $this->list_query($sql);
        return count($count);
    }

    /**
     * 获取粉丝数
     */
    public function getMemberFans($userid){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans WHERE attention_uid = {$userid}";
        $count = $this->list_query($sql);
        return count($count);
    }

    /**
     * getMemberAdopt
     */
    public function getMemberAdopt($userid){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer WHERE uid = {$userid}";
        $count = $this->list_query($sql);
        return count($count);
    }

    /**
     * Cancel Attention
     */
    public function cancelAttention($id){
        $sql = "DELETE FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans WHERE id = {$id}";
        if ($this->execute($sql)) {
            return 200;
        } else {
            return 219;
        }
    }

    /**
     * Invite Expert
     */
    public function getInviteExpert(){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_expert_profile AS a INNER JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS b ON a.userid = b.userid LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_invite as c ON c.to_uid = a.userid  WHERE a.status = 1";
        $data = $this->list_query($sql);
        return $data;
    }

    /**
     * 点赞设置
     */
    public function setAgree($userid,$status,$from_uid,$answer_id){
        if($status == 1){
            $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET agreed_times = agreed_times + 1 WHERE userid = {$userid}";
        }else{
            $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET agreed_times2 = agreed_times2 + 1 WHERE userid = {$userid}";
        }
        if ($this->execute($sql)) {
            $x = $this->addMessageAgree($from_uid,$userid,$answer_id);
            if($x){
                return 200;
            }else{
                return 215;
            }
        } else {
            return 220;
        }
    }

    /**
     * 添加点赞消息
     */
    public function addMessageAgree($from_uid,$to_uid,$answer_id)
    {
        $sql = "INSERT INTO {$this->tablePrefix}appknow_message_agree (from_uid,to_uid,answer_id,addtime,isread)VALUES({$from_uid},{$to_uid},{$answer_id},{$this->now},0)";
        return $this->execute($sql);
    }

    /**
     * 获取会员等级
     * @return mixed
     */
    public function getMemberGradeAll(){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_level";
        return $this->list_query($sql);
    }

    /**
     * 设置会员等级
     * @param $score 会员积分
     * @return mixed 返回用户等级
     */
    public function setMemberGrade($score){
        $data = $this->getMemberGradeAll();
        $num = count($data);
        foreach($data AS $key => $value){
            if($score >= $data[0]['credit']){
                if($data[$key]['levelid'] < $num){
                    if($score > $data[$key]['credit'] && $score <= $data[$key+1]['credit']){
                        $level['title'] = $data[$key+1]['leveltitle'];
                        $level['ico'] = $data[$key+1]['ico'];
                        break;
                    }else if($score > $data[$num-1]['credit']){
                        $level['title'] = $data[$num-1]['leveltitle'];
                        $level['ico'] = $data[$num-1]['ico'];
                        break;
                    }
                }
            }else{
                $level['title'] = $data[0]['leveltitle'];
                $level['ico'] = $data[0]['ico'];
            }
        }
        return $level;
    }

    /**
     * send sms mail
     * @param  string $content send message
     * @param  string $sendPhone send phone number
     * @param  int $stop is exit or not
     * @return int status
     */
    function sendSms($content,$sendPhone,$stop = 0){
        $content = urlencode($content);
        $cnsmsUrl = 'http://api.cnsms.cn/?ac=send&uid='.C('SMS_UID').'&pwd='.md5(C('SMS_PASSWORD')).'&mobile='.$sendPhone.'&content='.$content;
        if ($stop!=0) {
            exit();
        }
        return file_get_contents($cnsmsUrl);
    }

    /*
     * 消息统计封装
     * @param $table 表名
     * @param $uid   用户ID
     * @return mixed 返回sql语句
     */
    function mess_count($table = "",$uid = ""){
        $sql = "SELECT isread FROM ".C('DATABASE_MALL_TABLE_PREFIX')."{$table} WHERE to_uid = {$uid} ORDER BY id DESC limit 10";
        return $this->list_query($sql);
    }

    /**
     * 添加邀请专家
     */
    function addInviteExpert($info){
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_invite(from_uid,to_uid,askid,addtime)VALUES({$info['invitation_uid']},{$info['invite_uid']},{$info['ask_id']},{$this->now})";
        if($this->execute($sql)){
            return 200;
        }else{
            return 215;
        }
    }

    /**
     * 获取消息列表
     */
    public function getMessList($info){
        switch ($info['action']){
            case 'get_mess_tips': //通知
                $sql = "SELECT a.id,a.addtime,a.askid,a.isread,b.content,b.catid,c.mobile,d.nickname FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_reply AS a LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask AS b ON b.id = a.askid LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member AS c ON c.userid = a.from_uid LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS d ON d.userid = c.userid WHERE a.to_uid = {$info['userid']} ORDER BY a.id DESC LIMIT 10";
                break;

            case 'get_mess_invite': //邀请
                $sql = "SELECT a.id,a.addtime,a.askid,a.isread,b.content,b.catid,c.mobile,d.nickname FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_invite AS a LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask AS b ON b.id = a.askid LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member AS c ON c.userid = a.from_uid LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS d ON d.userid = c.userid WHERE a.to_uid = {$info['userid']} ORDER BY a.id DESC LIMIT 10";
                break;

            case 'get_mess_agree': //点赞
                $sql = "SELECT a.id,a.addtime,a.isread,b.content,c.mobile,d.nickname FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_agree AS a LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer AS b ON b.id = a.answer_id LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member AS c ON c.userid = a.from_uid LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS d ON d.userid = c.userid WHERE a.to_uid = {$info['userid']} ORDER BY a.id DESC LIMIT 10";
                break;

            case 'get_mess_attention': //关注
                $sql = "SELECT a.id,a.addtime,a.isread,b.mobile,c.nickname FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_attention AS a LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member AS b ON b.userid = a.from_uid LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS c ON c.userid = b.userid WHERE a.to_uid = {$info['userid']} ORDER BY a.id DESC LIMIT 10";
                break;

            default:
                break;
        }
        //$this->putLog('sql',$sql);
        return $this->list_query($sql);
    }

    public function isRead($info){
        switch ($info['opt']){
            case 'get_mess_tips':
                $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_reply SET isread = 1 WHERE id = {$info['id']}";
                break;
            case 'get_mess_invite':
                $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_invite SET isread = 1 WHERE id = {$info['id']}";
                break;
            case 'get_mess_agree':
                $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_agree SET isread = 1 WHERE id = {$info['id']}";
                break;
            case 'get_mess_attention':
                $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_attention SET isread = 1 WHERE id = {$info['id']}";
                break;
            default:
                break;
        }

        if($this->execute($sql)){
            return 200;
        }else{
            return 220;
        }
    }

    //获取最新版本
    public function version($ver){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_version ORDER BY id DESC LIMIT 1 ";
        $version = $this->list_query($sql);
        if($version[0]['version'] > $ver){
            return 1;
        }else{
            return 0;
        }
    }

}
