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
        'sa_login' => 1,            //用户登录 +1
        'sa_questions' => 1,        //用户提问问题 +1
        'sa_answer' => 2,           //用户回答问题 +2
        'sa_share' => 10,           //每天分享微信、朋友圈、QQ空间 +50
        'sa_code' => 100,           //推荐好友并填写个人推荐码各得 +100
        'sa_agree_times' => 1,      //被点赞得积分 同意 +1
        'sa_against_times' => 1,    //被点赞得积分 不同意 -1 【暂定】
        'sa_profile' => 20,         //完善个人资料得积分 +10
        'sa_feedback' => 2          //一键反馈得积分 +2
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
                        $b[$k][nickname] = $this->mobileHide($y[0][mobile]);
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
                $x[$k]['member_profile'] = ['area_name'=>'暂无','nickname'=>$this->mobileHide($v['mobile']),'avatar'=>'Uploads/image/defaultx20.jpg'];
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
            //$this->putLog('sql',$sql);
            return $this->execute($sql);
        }
    }

    /**
     * 完善个人信息状态
     * @param $userid 用户ID
     * @return mixed
     */
    public function profileIsOk($userid){
        if($userid){
            $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET is_ok = 1 WHERE userid = {$userid}";
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
            return $this->execute($sql);
        }
    }

    /**
     * 设置农医问药用户资料
     * @param $userid
     */
    public function setAppknowMemberProfile($info)
    {
        $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET nickname='{$info[nickname]}',sex={$info[sex]},qq='{$info[qq]}',truename='{$info[truename]}',areaid={$info[areaid]},address='{$info[address]}',location='{$info[location]}',instro='{$info[instro]}' WHERE userid = {$info[userid]}";

        if ($this->getAppknowMemberProfile($info[userid])) { //存在修改
            return $this->execute($sql);
        }else{ //不存在添加
            $result = $this->setMemberProfile($info[userid]); //添加userid到个人资料表
            if($result){
                return $this->execute($sql);
            }
        }
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
    public function getAskList($start = null, $limit = null,$cat_id = null,$keyword = null)
    {
        $where = "WHERE 1=1";
        if($cat_id != null){
            $where .= " AND catid = {$cat_id}";
        }
        if($keyword != null){
            //单条搜索
            //$where .= " AND content like '%{$keyword}%'";

            //分词搜索
            $arr_keyword = explode(',',$this->scws($keyword));
            $str = " AND ";
            foreach ($arr_keyword AS $k=>$v){
                $str .= " content like '%{$arr_keyword[$k]}%' OR";
            }
            $where .= substr($str,0,-2);
        }

        $sql = "SELECT ask.*,profile.nickname,profile.truename,profile.areaid,profile.address,profile.avatar,profile.location,m_member.mobile FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask AS ask
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
            $x[$k]['keyword'] = implode(',',$arr_keyword);
        }
        return $x;
    }

    /**
     * 获取问题详情
     */
    public function getAskInfo($askid)
    {
        $sql = "SELECT ask.*,m_member.mobile,profile.nickname,profile.truename,profile.areaid,profile.address,profile.avatar
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
        $sql = "SELECT answer.*,m_member.mobile,profile.nickname,profile.truename,profile.areaid,profile.avatar FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer AS answer LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member AS m_member ON answer.uid = m_member.userid LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS profile ON answer.uid = profile.userid WHERE answer.askid = {$askid} ORDER BY addtime ASC";

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
        if(empty($info[reply_nickname])||$info[reply_nickname] == null){
            $reply_nickname = 0;
        }else{
            $reply_nickname = $info[reply_nickname];
        }

        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer (uid,reply_nickname,askid,content,addtime) VALUES ({$info[userid]},'{$reply_nickname}',{$info[askid]},'{$info[content]}',{$this->now})";
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
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_favourite (uid,to_uid,type,cat_id,obj_id,addtime) VALUES ({$info['userid']},{$info['to_uid']},{$this->favourite_type[$info['type']]},{$info['cat_id']},{$info['obj_id']},{$this->now})";
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
        $sql = "SELECT fav.*,m_profile.truename,m_profile.nickname,m_profile.avatar FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_favourite AS fav
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

    //获取个人收藏列表
    public function getSimpleFavourite($userid){
        $sql = "SELECT a.*,b.truename,b.nickname,b.avatar,c.content AS obj_content from destoon_appknow_member_favourite AS a LEFT JOIN destoon_appknow_member_profile AS b ON b.userid = a.to_uid LEFT JOIN destoon_appknow_question_ask AS c ON c.id = a.obj_id WHERE a.uid = {$userid} ORDER BY a.addtime DESC";
        return $this->list_query($sql);
    }

    /**
     * 提问历史
     */
    public function myAskHistory($userid)
    {
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask WHERE uid = {$userid} ORDER BY addtime DESC";
        $x = $this->list_query($sql);
        foreach ($x as $k => $v) {
            $x[$k]['addtime'] = date("Y-m-d", $v['addtime']);
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
            $x[$k]['addtime'] = date("Y-m-d", $v['addtime']);
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

        if ($this->getFetchAttention($info[attention_uid], $info[fans_uid]) > 0) {
            return 217;
            exit();
        }

        //关注消息设置
        $this->addMessageAttention($info[fans_uid],$info[attention_uid]);

        //粉丝设置
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans (attention_uid,fans_uid,addtime) VALUES ({$info[attention_uid]},{$info[fans_uid]},{$this->now})";

        if($this->execute($sql)){
            return 200;
        }
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
            if ($fans_uid!=null) {
                $fans_detail = $this->getUserDetail($v[fans_uid], array('member_profile', 'expert_profile'));
                $x[$k]['fans_detail'] = $fans_detail[0];
            }

            if($attention_uid!=null) {
                $attention_detail = $this->getUserDetail($v[attention_uid], array('member_profile', 'expert_profile'));
                $x[$k]['attention_detail'] = $attention_detail[0];

                if($x[$k]['attention_detail']['member_type'] != $type){
                    unset($x[$k]);
                }
            }

        }
        return $x;
    }

    /**
     * 获取是否关注
     */
    public function getFetchAttention($attention_uid = null, $fans_uid = null)
    {
        $sql = "SELECT COUNT(*) AS c FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans WHERE attention_uid = {$attention_uid} AND fans_uid = {$fans_uid}";
        $x = $this->list_query($sql);
        return $x[0]['c'];
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
        $sql = "SELECT a.cat_id,b.cat_name,b.cat_img FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_selected_category AS a INNER JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_category AS b ON a.cat_id=b.id WHERE a.uid = {$userid}";
        $data = $this->list_query($sql);
        return $data;
    }

    /**
     * 取消我的圈子关注
     * @param $userid
     */
    public function cancelMyCategory($id,$userid){
        if (!empty($userid)){
            $sql = "DELETE FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_selected_category WHERE uid = {$userid} AND cat_id = {$id}";
            if($this->execute($sql)){
                return 200;
            }else{
                return 219;
            }
        }else{
            return 201;
        }
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
    public function getInviteExpert($info){
//        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_expert_profile AS a INNER JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile AS b ON a.userid = b.userid LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_invite as c ON c.to_uid = a.userid  WHERE a.status = 1 GROUP BY a.userid ORDER BY b.score DESC";
//        $data = $this->list_query($sql);
//        return $data;

        $sql = "SELECT a.userid,a.`name`,a.good_at_crop,b.nickname,b.avatar,b.truename,c.to_uid FROM destoon_appknow_expert_profile AS a LEFT JOIN destoon_appknow_member_profile AS b ON b.userid = a.userid LEFT JOIN destoon_appknow_message_invite AS c ON c.to_uid = a.userid WHERE a.`status` = 1 GROUP BY a.userid ORDER BY b.score DESC";
        $data = $this->list_query($sql);

        foreach ($data as $k=>$v){
            $sql2 = "select sum(agree_times) as agreed_times from destoon_appknow_question_answer where uid = {$v['userid']} and agree_times > 0";

            $sql3 = "SELECT count(*) AS fans_nums FROM `destoon_appknow_member_fans` WHERE attention_uid = {$v['userid']};";

            $data2 = $this->list_query($sql2);
            $data3 = $this->list_query($sql3);

            $data[$k] = $v;
            $data[$k]['agreed_times'] = $data2[0]['agreed_times'];
            $data[$k]['fans_nums'] = $data3[0]['fans_nums'];

            $arr = array();
            $arr['invitation_uid'] = $info['invitation_uid'];       //当前用户ID
            $arr['invite_uid'] = $v['userid'];                       //专家ID
            $arr['ask_id'] = $info['ask_id'];                         //问答ID

            //判断是否邀请过
            if($this->checkInviteExpert($arr) == 217){
                $data[$k]['status'] = 1;
            }else{
                $data[$k]['status'] = 0;
            }
        }
        return $data;
    }

    /**
     * 我的邀请列表
     * @param $userid
     */
    public function getMyInviteExpertList($userid){
        $sql = "SELECT a.addtime,a.isread,b.name,c.nickname,c.avatar,d.id,d.content,d.catid FROM destoon_appknow_message_invite AS a LEFT JOIN destoon_appknow_expert_profile AS b ON b.userid = a.to_uid LEFT JOIN destoon_appknow_member_profile c ON c.userid = b.userid LEFT JOIN destoon_appknow_question_ask AS d ON d.id = a.askid WHERE a.from_uid = {$userid} ORDER BY addtime DESC";
        return $this->list_query($sql);
    }

    /**
     * 点赞设置
     */
    public function setAgree($userid,$status,$from_uid,$answer_id){
        if($status == 1){
//            $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET agreed_times = agreed_times + 1 WHERE userid = {$userid}";

            $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer SET agree_times = agree_times + 1 WHERE uid = {$userid} AND id = {$answer_id}";

        }else{
            $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer SET against_times = against_times + 1 WHERE uid = {$userid} AND id = {$answer_id}";
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
        $sql = "SELECT isread FROM ".C('DATABASE_MALL_TABLE_PREFIX')."{$table} WHERE to_uid = {$uid} OR to_uid = 0 ORDER BY id DESC limit 10";
        return $this->list_query($sql);
    }

    /**
     * 添加邀请专家
     */
    function addInviteExpert($info){
        //判断是否重复邀请
        if($this->checkInviteExpert($info) == 217){
            return 217;
        }else{
            $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_invite(from_uid,to_uid,askid,addtime)VALUES({$info['invitation_uid']},{$info['invite_uid']},{$info['ask_id']},{$this->now})";
            if($this->execute($sql)){
                return 200;
            }else{
                return 215;
            }
        }
    }

    /**
     * 检查是否已经邀请过专家
     * @param $info
     */
    function checkInviteExpert($info){
        $sql = "SELECT COUNT(*) AS count FROM destoon_appknow_message_invite WHERE from_uid = {$info['invitation_uid']} AND to_uid = {$info['invite_uid']} AND askid = {$info['ask_id']}";
        $result = $this->list_query($sql);
        if($result[0]['count'] > 0){
            return 217;
            exit;
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
            case 'get_mess_sys': //系统消息
                $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_sys WHERE to_uid = {$info['userid']} OR to_uid = 0 ORDER BY id DESC LIMIT 10";
                break;
            default:
                break;
        }
        //$this->putLog('sql',$sql);
        return $this->list_query($sql);
    }

    //判断是否已经阅读
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
            case 'get_mess_sys':
                $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_message_sys SET isread = 1 WHERE id = {$info['id']}";
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

    //会员中心获取点赞个数
    public function getAnswerAgree($uid){
        $sql = "SELECT SUM(agree_times) AS count FROM destoon_appknow_question_answer WHERE uid = {$uid} AND agree_times > 0";
        return $this->list_query($sql);
    }

    //获取最新版本
    public function version($ver){
        $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_version ORDER BY id DESC LIMIT 1 ";
        $version = $this->list_query($sql);
        if($version[0]['version'] > $ver){
            return $version;
        }
    }

    //邀请码生成函数
    public function ApplyCodeRand($length, $chars = '0123456789abcdefghijklmnopqrstuvwxyz'){
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    //设置邀请码
    public function setApplyCode($uid){
        $code = $this->ApplyCodeRand(7);
        $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET apply_code = '{$code}' WHERE userid = {$uid}";
        if($this->execute($sql)){
            return 1;
        }else{
            return 0;
        }
    }

    //获取邀请码
    public function getApplyCode($uid){
        $sql = "SELECT apply_code FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile WHERE userid = {$uid}";
        return $this->list_query($sql);
    }

    //判断邀请码是否存在
    public function checkApplyCode($code){
        $sql = "SELECT count(*) AS count FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile WHERE apply_code = '{$code}'";
        $data = $this->list_query($sql);
        if($data[0]['count'] > 0){
            return 1;  //已存在
        }else{
            return 0;  //未存在
        }
    }

    //添加邀请码
    public function addApplyCode($apply_code,$uid){
        $sql = "INSERT INTO ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_code(apply_code,uid,addtime)VALUES('{$apply_code}',{$uid},{$this->now})";
        $this->execute($sql);
    }

    /**
     * 邀请码加积分
     * @param $userid 新注册会员ID
     * @param $code   邀请码会员
     */
    public function addApplyCodeScore($userid,$code){
        $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET score = score + 100 WHERE apply_code = '{$code}'";

        $sql2 = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_profile SET score = score + 100 WHERE userid = '{$userid}'";

        $this->execute($sql);
        $this->execute($sql2);
    }

    //获取我的邀请列表
    public function getMyApplyCode($code){
        $sql = "SELECT b.mobile,b.addtime FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_code AS a LEFT JOIN ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member AS b ON b.userid = a.uid WHERE a.apply_code = '{$code}'";
        $data = $this->list_query($sql);
        foreach ($data AS $key=>$value){
            $data[$key]['addtime'] = date('Y-m-d',$value['addtime']);
        }
        return $data;
    }

    /**
     * 获取用户信息
     * @param $uid 用户ID
     * @param $type 类型  0 关注  1 粉丝
     * @return mixed
     */
    public function getUserInfo($uid,$type){
        switch($type){
            case 'attention':
                $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans WHERE attention_uid = {$uid}";
                break;

            case 'fans':
                $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_member_fans WHERE fans_uid = {$uid}";
                break;
            case 'ask':
                $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_ask WHERE uid = {$uid}";
                break;

            case 'answer':
                $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer WHERE uid = {$uid}";
                break;

            case 'agree_times':
                $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer WHERE uid = {$uid} AND agree_times > 0";
                break;

            case 'against_times':
                $sql = "SELECT * FROM ".C('DATABASE_MALL_TABLE_PREFIX')."appknow_question_answer WHERE uid = {$uid} AND against_times > 0";
                break;

            default:
                break;
        }

        return $this->list_query($sql);
    }

    /**
     * 中文分词处理方法
     *+---------------------------------
     * @author Nzing
     * @access public
     * @version 1.0
     *+---------------------------------
     * @param stirng  $string 要处理的字符串
     * @param boolers $sort=false 根据value进行倒序
     * @param Numbers $top=0 返回指定数量，默认返回全部
     *+---------------------------------
     * @return void
     */
    public function scws($text, $top = 5, $return_array = false, $sep = ',') {
        include('pscws4.php');
        $cws = new \pscws4('utf-8');
        $cws -> set_charset('utf-8');
        $cws -> set_dict(dirname(__FILE__) .'/etc/dict.utf8.xdb');
        $cws -> set_rule(dirname(__FILE__) .'/etc/rules.utf8.ini');
        $cws -> set_ignore(true);
        $cws -> send_text($text);
        $ret = $cws -> get_tops($top, 'r,v,p');
        $result = null;
        foreach ($ret as $value) {
            if (false === $return_array) {
                $result .= $sep . $value['word'];
            } else {
                $result[] = $value['word'];
            }
        }
        return false === $return_array ? substr($result, 1) : $result;
    }

    /**
     * 获取关键词
     */
    public function getKeyWord(){
//        if(!S('keyword')){
//            $sql = "SELECT * FROM destoon_appknow_keyword";
//            $data = $this->list_query($sql);
//            S('keyword',$data,0); //默认关键词缓存60秒
//        }

        $sql = "SELECT * FROM destoon_appknow_keyword";
        return $this->list_query($sql);
    }

    /**
     * 遍历循环关键词
     * @param $content 循环内容
     */
    public function eachKeyWord($content){
        $key = $this->getKeyWord();
        foreach ($key AS $k=>$v){
            $content = str_replace($v["keyword"],"<a href='http://www.nongyao001.com/sell/search.php?uagent=touch&searchid=5&kw=".urlencode(iconv('utf-8','gb2312',$v["keyword"]))."' style='color:red'>".$v["keyword"]."</a>",$content);
        }
        return $content;
    }

    /**
     * 注册时 设置用户ID
     * @param $userid
     */
    public function setMemberProfile($userid){
        $sql = "INSERT INTO destoon_appknow_member_profile(userid)VALUES({$userid})";
        $this->execute($sql);
    }

    /**
     * 获取用户是否完善个人信息
     * @param $userid  用户ID
     * @return int     返回 int 【1 已经完善   0 未完善】
     */
    public function getMemAreaId($userid){
        $sql = "SELECT areaid FROM destoon_appknow_member_profile WHERE userid = {$userid}";
        $data = $this->list_query($sql);
        if($data[0]['areaid'] > 0){
            return 1;  //已存在
        }else{
            return 0;  //未存在
        }
    }

    /**
     * 时间转换函数
     * @param $the_time 时间字符串
     * @return string 返回时间字符串
     */
    function time_tran($the_time) {
        $now_time = date("Y-m-d H:i:s", time());
        $now_time = strtotime($now_time);
        $show_time = strtotime($the_time);
        $dur = $now_time - $show_time;
        if ($dur < 0) {
            return $the_time;
        } else {
            if ($dur < 60) {
                return $dur . '秒前';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) {//3天内
                            return floor($dur / 86400) . '天前';
                        } else {
                            return $the_time;
                        }
                    }
                }
            }
        }
    }

    /**
     * 时间转换函数 2
     * @param $time     时间戳
     * @return string   返回时间字符串
     */
    function format_date($time){
        $t=time()-$time;
        $f=array(
            '31536000'=>'年',
            '2592000'=>'个月',
            '604800'=>'星期',
            '86400'=>'天',
            '3600'=>'小时',
            '60'=>'分钟',
            '1'=>'秒'
        );
        foreach ($f as $k=>$v)    {
            if (0 !=$c=floor($t/(int)$k)) {
                return $c.$v.'前';
            }
        }
    }
}
