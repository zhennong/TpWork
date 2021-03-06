<?php
namespace Home\Controller;

header('Access-Control-Allow-Origin: *');

use Common\Controller\CommonController;
use Common\Jpush;
use Common\Tools;
use Home\Api\ApiAppKnow;

class IndexController extends CommonController {

    //农医问药接口
    public function index(){
        // 返回函数
        $jsoncallback = I('get.jsoncallback');
        $api = new ApiAppKnow();

        $show['status'] = 200;
        //$api->putLog('data',I());
        switch (I('get.action')) {
            // 登陆
            case 'login':
                $password = md5(md5(I('get.password')));
                $x = $api->getUserFromMobile(I('get.mobile'));
                $show['member_info'] = $x;
                if ($x[0]['password'] == $password) {
                    if($x[0]['status'] != 0){
                        $show['member_info'] = $x;
                        $api->getLastLoginTime($x[0]['userid']);

                        $show['info'] = $api->getMemAreaId($x[0]['userid']);

                        //登录积分设置 每天累加一分
                        $last_time = $x[0]['last_login_time'];
                        $timestamp = time() - $last_time;
                        //$api->putLog('sql',$timestamp);
                        if($timestamp > 86400){  //24h 后才可以累加积分
                            $api->addScore($x[0]['userid'],'sa_login');
                        }

                    }else{
                        $show['status'] = 222;
                    }
                } else {
                    $show['status'] = 204;
                }

                break;

            // 获取注册码
            case 'get_register_code':
                if ($api->getUserFromMobile(I('get.mobile'))) {
                    $show['status'] = 202;
                } else {
                    $k = md5(I('get.mobile')) . 'register_code';
                    if (S($k)) {
                        $show['status'] = 209;
                    } else {
                        $x = rand(100000, 999999);
                        S($k, $x, 120);
                        if ($y = $api->sendSms($x, I('get.mobile'))) {

                        } else {
                            $show['status'] = 206;
                        }
                    }
                }
                break;

            // 注册
            case 'register':
                $password = md5(md5(I('get.password')));
                $k = md5(I('get.mobile')) . 'register_code';
                if ($api->getUserFromMobile(I('get.mobile'))) {
                    $show['status'] = 202;
                } else {
                    if (S($k) == I('get.yzm')) {
                        $show['status'] = $api->register(I('get.'));
                        if ($show['status'] = 200) {
                            $show['member_info'] = $api->getUserFromMobile(I('get.mobile'));
                            
                            //默认添加信息到个人资料表
                            $api->setMemberProfile($show['member_info'][0]['userid']);

                            //判断邀请码是否存在
                            $code = $api->checkApplyCode(I('get.apply_code'));
                            if($code == 1){
                                // 双方各加 100积分
                                $api->addApplyCodeScore($show['member_info'][0]['userid'],I('get.apply_code'));

                                // 邀请信息添加到邀请表
                                $api->addApplyCode(I('get.apply_code'),$show['member_info'][0]['userid']);
                            }
                        }
                    }else{
                        $show['status'] = 223;
                    }
                }
                break;

            // 重置密码
            // 获取验证码
            case 'get_code':
                if ($api->getUserFromMobile(I('get.mobile'))) {
                    $k = md5(I('get.mobile')) . 'reset_password_code';
                    if (S($k)) {
                        $show['status'] = 209;
                    } else {
                        $x = rand(100000,999999);
                        S($k, $x, 120);
                        if ($y = $api->sendSms($x, I('get.mobile'))) {

                        } else {
                            $show['status'] = 206;
                        }
                    }
                } else {
                    $show['status'] = 205;
                }
                break;

            // 验证验证码
            case 'validate_code':
                $k = md5(I('get.mobile')) . 'reset_password_code';
                if (S($k) == I('get.code')) {

                } else {
                    $show['status'] = 207;
                }
                break;

            // 重置新密码
            case 'reset_password':
                $password = md5(md5(I('get.password')));
                $mobile = I('get.mobile');
                $sql = "UPDATE ".C('DATABASE_MALL_TABLE_PREFIX')."ucenter_member SET password = '{$password}' WHERE mobile = '{$mobile}'";
                if ($api->execute($sql)) {
                } else {
                    $show['status'] = 208;
                }
                break;

            // 获取地区信息
            case 'get_area_info':
                $show['area_info'] = $api->getAreaInfo(I('get.pid'));
                if (!$show['area_info']) {
                    $show['status'] = 210;
                }
                break;

            // 获取地区父级节点
            case 'get_area_parent_nodes':
                $allArea = $api->getAllArea();
                $x = Tools::get_list_parents($allArea, I('get.areaid'), 'areaid', 'parentid');
                $show['parent_areas'] = $x;
                break;

            // 获取自定义栏目信息
            case 'get_ask_category_info':
                $show['category_info'] = $api->getAskCategoryInfo(I('get.pid'));
                if (!$show['category_info']) {
                    $show['status'] = 210;
                }
                break;

            // 获取个人信息
            case 'get_member_profile':
                $show['member_profile'] = $api->getUserDetail(I('get.userid'),array('member_profile','expert_profile'));
                if (!$show['member_profile']) {
                    $show['status'] = 211;
                }

                $allArea = $api->getAllArea();
                $x = Tools::get_list_parents($allArea, I('get.areaid'), 'areaid', 'parentid');
                $show['parent_areas'] = $x;
                break;

            // 设置个人信息
            case 'set_member_profile':
                $info = I('get.');
                $x = $api->setAppknowMemberProfile($info);
                if (!$x) {
                    $show['status'] = 212;
                }else{
                    $show['member_profile'] = $api->getUserDetail(I('get.userid'),array('member_profile','expert_profile'));

                    //登录积分设置 每天累加一分
                    $is_ok = $show['member_profile'][0]['is_ok'];
                    if($is_ok == 0){
                        $api->addScore(I('get.userid'),'sa_profile');
                        $api->profileIsOk(I('get.userid')); //完善信息状态修改
                    }

                    $api->setApplyCode($show['member_profile'][0]['userid']);  //设置邀请码
                }
                break;

            // 上传会员图片
            case 'upload_avatar_image':
                $_dir = C('UPLOADS').'avatar/';
                $extend_type = Tools::get_extend($_FILES['filename']['name']);
                $upload_dir = APP_ROOT . $_dir;
                $upload_name = "user_" . I('get.userid') . "_" . date("Y_m_d__H_i_s", time());
                $show['status'] = ApiAppKnow::uploadImage($_FILES['filename'], $upload_dir, $upload_name);
                if ($show['status'] == 200) {
                    $info = array('userid' => I('get.userid'), 'avatar' => $_dir . $upload_name . "." . $extend_type);
                    if ($api->setAppknowMemberAvatar($info)) {
                        $show['image_path'] = APP_ROOT . $info['avatar'];
                    } else {
                        $show['status'] = 212;
                    }
                }
                break;

            // 上传问答图片
            case 'upload_ask_image':
                if (I('get.userid')) {
                    $_dir = C('UPLOADS').'ask/';
                    $extend_type = Tools::get_extend($_FILES['filename']['name']);
                    $upload_dir = APP_ROOT . $_dir;
                    $upload_name = "user_" . I('get.userid') . "_" . date("Y_m_d__H_i_s", time());
                    $show['status'] = ApiAppKnow::uploadImage($_FILES['filename'], $upload_dir, $upload_name);
                    if ($show['status'] == 200) {
                        $ask_img_path = $_dir . $upload_name . "." . $extend_type;
                        $k = "user_ask_image_" . I('get.userid');
                        $k1 = $k . "_ask_tmp_image";
                        if (count(Tools::str2arr(S($k1))) <= 6) {
                            if (S($k1)) {
                                $x = S($k1);
                                $x = $x . "," . $ask_img_path;
                                S($k1, $x, 300);
                            } else {
                                S($k1, $ask_img_path, 300);
                            }
                        }
                        $show[$k] = Tools::str2arr(S($k1));
                    }
                } else {
                    $show['status'] = 214;
                }
                break;

            // 重选图片
            case 'reset_ask_images':
                $k = "user_ask_image_" . I('get.userid');
                $k1 = $k . "_ask_tmp_image";
                S($k1, null);
                break;

            // 提交问答
            case 'submit_quick_ask':
                $k = "user_ask_image_" . I('get.userid');
                $k1 = $k . "_ask_tmp_image";
                $show[$k] = Tools::str2arr(S($k1));
                foreach ($show[$k] as $k1 => $v1) {
                    $info['thumb' . $k1] = $v1;
                }
                $info['uid'] = I('get.userid');
                $info['content'] = I('get.content');
                $info['cat_id'] = I('get.cat_id');

                $info['score'] = I('get.score') != ''? I('get.score') : 0; //兼容老版本 判断是否存在score 没有默认为 0

                $score = $api->getMemberScore(I('get.userid'));
                if($info['score'] > $score){
                    return 224;
                    exit;
                }
                if ($api->addQuickAsk($info)) {
                    $api->addScore(I('get.userid'),'sa_questions');

                    $k = "user_ask_image_" . I('get.userid');
                    $k1 = $k . "_ask_tmp_image";
                    S($k1, null);
                } else {
                    $show['status'] = 215;
                }
                break;

            // 获取问答图片
            case 'get_ask_images':
                if (I('get.userid')) {
                    $k = "user_ask_image_" . I('get.userid');
                    $k1 = $k . "_ask_tmp_image";
                    $show[$k] = Tools::str2arr(S($k1));
                }
                break;

            // 获取问答列表
            case 'get_ask_list':
                $x = $api->getAskList(I('get.start'), I('get.limit'), I('get.cat_id'), I('get.keyword'),I('get.userid'));
                foreach ($x as $k => $v) {
                    $x[$k]['addtime_date'] = $api->format_date($v['addtime']);
                    for ($i = 0; $i < 6; $i++) {
                        if ($v['thumb' . $i]) {
                            $x[$k]['ask_images'][] = $v['thumb' . $i];
                            $x[$k]['image_count'] = $i + 1;
                        }
                    }
                    $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),' ');
                }
                $show['ask_list'] = $x;
                break;

            // 获取问题详情 (兼容老版本问题详情接口【请勿删除】)
            case "get_ask_info":
                $x = $api->getAskInfo(I('get.askid'),I('get.userid'));
                foreach ($x as $k => $v) {
                    $x[$k]['addtime_date'] = $api->format_date($v['addtime']);
                    for ($i = 0; $i < 6; $i++) {
                        if ($v['thumb' . $i]) {
                            $x[$k]['ask_images'][] = $v['thumb' . $i];
                            $x[$k]['image_count'] = $i + 1;
                        }
                    }
                    $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),'');
                }
                $show['ask_info'] = $x;

                //问答列表
                $x = $api->getAskAnswers(I('get.askid'),null,null,I('get.userid'));
                foreach ($x as $k => $v) {
                    $x[$k]['addtime_date'] = $api->format_date($v['addtime']);
                    $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),'');
                    $x[$k]['content'] = $api->eachKeyWord($v['content']); //获取关键词加链接
                }
                $show['question_answers'] = $x;
                break;

            // 获取问题详情 (新版接口)
            case "get_ask_arc_info";
                //获取问答信息
                $x = $api->getAskInfo(I('get.askid'),I('get.userid'));
                foreach ($x as $k => $v) {
                    $x[$k]['addtime_date'] = $api->format_date($v['addtime']);
                    for ($i = 0; $i < 6; $i++) {
                        if ($v['thumb' . $i]) {
                            $x[$k]['ask_images'][] = $v['thumb' . $i];
                            $x[$k]['image_count'] = $i + 1;
                        }
                    }
                    $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),'');
                }
                $show['ask_info'] = $x;

                //问答列表
                $x = $api->getAskArcAnswers(I('get.askid'),null,null,I('get.userid'));
                foreach ($x as $k => $v) {
                    $x[$k]['addtime_date'] = $api->format_date($v['addtime']);
                    $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),'');
                    $x[$k]['content'] = $api->eachKeyWord($v['content']); //获取关键词加链接
                }
                foreach ($x as $k=>$v){
                    $y[] = $v;
                }

                //$api->putLog("回复内容：".$y);

                $show['question_answers'] = $y;
                //获取回复信息

                break;

            // 我要回答 && 保存回答消息
            case "submit_questions_answer":
			    //增加回复消息数量
                $resultId = $api->addQuestionAnswer(I('get.'));
//                $api->putLog("添加回复：".$resultId);
                if ($resultId > 0) {
				   //统计回复消息数top_ier
					D('QuestionAsk')->where(array("id"=>$resultId))->setInc('answer_number',1);

                    //积分累加
                    $api->addScore(I('get.userid'),'sa_answer');

                    //回复消息设置
                    $api->addMessageReply(I('get.'));

                }else {
                    $show['status'] = 215;
                }
                break;

            // 意见反馈（2016.02.16）
            case 'submit_feed_back':
                if (!$api->addFeedBack(I('get.'))) {
                    $show['status'] = 215;
                }else{
                    $api->addScore(I('get.userid'),'sa_feedback');
                }
                break;

            // 上传身份证正面照（2016.02.16）
            case 'upload_id_card_front':
                if (!I('get.userid')) {
                    $show['status'] = 213;
                } else {
                    $_dir = C('UPLOADS').'front/';
                    $extend_type = Tools::get_extend($_FILES['filename']['name']);
                    $upload_dir = APP_ROOT . $_dir;
                    $upload_name = "user_" . I('get.userid') . "_" . date("Y_m_d__H_i_s", time());
                    $show['status'] = ApiAppKnow::uploadImage($_FILES['filename'], $upload_dir, $upload_name);
                    if ($show['status'] == 200) {
                        $info = array('card_front_path' => $_dir . $upload_name . "." . $extend_type);
                        echo $show['save_path'] = $info['card_front_path'];
                        exit();
                    }else {
                        $show['status'] = 101;
                    }
                }
                break;

            // 上传身份证反面照（2016.02.16）
            case 'upload_id_card_back':
                if (!I('get.userid')) {
                    $show['status'] = 213;
                } else {
                    $_dir = C('UPLOADS').'back/';
                    $extend_type = Tools::get_extend($_FILES['filename']['name']);
                    $upload_dir = APP_ROOT . $_dir;
                    $upload_name = "user_" . I('get.userid') . "_" . date("Y_m_d__H_i_s", time());
                    $show['status'] = ApiAppKnow::uploadImage($_FILES['filename'], $upload_dir, $upload_name);
                    if ($show['status'] == 200) {
                        $info = array('card_back_path' => $_dir . $upload_name . "." . $extend_type);
                        echo $show['save_path'] = $info['card_back_path'];
                        exit();
                    }else {
                        $show['status'] = 101;
                    }
                }
                break;

            // 专家认证状态（2016.02.17）
            case 'check_expert_authentication':
                $show['expert_info'] = $api->getExpertProfile(I('get.userid'));
                break;

            //专家认证（2016.02.16）
            case 'submit_expert_authentication':
                if (!$api->addExpertAuthentication(I('get.'))) {
                    $show['status'] = 215;
                }
                break;
            //获取收藏（详情页）
            case 'get_favouriteinfo':
                $show['status'] = $api->getFavouriteInfo(I('get.'));
                break;

            //加入收藏
            case 'add_favourite':
                $show['status'] = $api->addFavourite(I('get.'));
                break;
            //（内容页）取消加入收藏
            case 'remove_favourite':
                $show['status'] = $api->removeFavourite(I('get.'));
                break;

            //显示收藏
            case 'get_favourite_list':
                $data = $api->getSimpleFavourite(I('get.userid'));
                foreach ($data AS $k=>$v){
                    $data[$k]['addtime'] = date("Y-m-d",$v['addtime']);
                }
                $show['favourite_info'] = $data;
                break;

            //提问历史
            case 'my_ask_history':
                $show['ask_historys'] = $api->myAskHistory(I('get.userid'));
                break;

            //提问历史 （新接口）
            case 'my_ask_history_new':
                $data = D('QuestionAsk')->getUidByAskList(I('get.userid'));

                $show['ask_history_list'] = $data;
                break;

            //问答历史
            case 'my_answer_history':
                $show['answer_historys'] = $api->myAnswerHistory(I('get.userid'));
                break;

            //问答历史 （新接口）
            case 'my_answer_history_new':
                $data = D('QuestionAsk')->getUidByAnswerList(I('get.userid'));
                if(empty($data)){
                    $data = array();
                }
                $show['answer_history_list'] = $data;
                break;

            //添加关注
            case 'add_attention':
                $info = $_GET;
                $show['status'] = $api->addAttention($info);
                break;

            //我的关注
            case 'my_attention':
                $info = I('get.');
                $show['attention_list'] = $api->getAttention(null, $info['userid'], $info['type']);
                break;

            //我的粉丝（2016.02.17）
            case 'my_fans':
                if (I('get.userid')) {
                    $info = I('get.');
                    $show['fans_list'] = $api->getFans($info['userid'], null, $info['type']);
                }
                break;

            // 我的关注 （新接口）
            case 'my_attention_new':
                $info = I('get.');
                $atten_list = $api->getAttentionList($info['userid'],$info['type']);
                if(count($atten_list) == 0){
                    $atten_list = array();
                }
                $show['attention_list'] = $atten_list;
                break;

            // 我的粉丝 （新接口）
            case 'my_fans_new':
                $info = I('get.');
                $fans_list = $api->getFansList($info['userid'],$info['type']);
                if(count($fans_list) == 0){
                    $fans_list = array();
                }
                $show['fans_list'] = $fans_list;
                break;

            //获取分类
            case 'get_category_list':
                $show['category_list_info'] = $api->getCategoryList(I('get.id'), I('get.pid'),null,null,1,I('get.userid'));
                break;

            //获取热门分类
            case 'get_hot_category':
                $show['hot_category_list'] = $api->getCategoryList(null,null,null,null,2);
                break;

            //添加个人自定义分类
            case 'add_select_category':
                $info = I('get.');
                $show[status] = $api->addSelectCategory($info);
                break;

            //获取个人自定义分类
            case 'get_my_category_list':
                $my_category = $api->getMyCategoryList(I('get.userid'));
                foreach ($my_category AS $k=>$v){
                    $my_category[$k]['id'] = $v['cat_id'];
                }

                $show['my_category_list'] = $my_category;

                break;
            
            case 'cancel_my_category':
                $show['status'] = $api->cancelMyCategory(I('get.id'),I('get.userid'));
                break;

            //获取圈子信息
            case "get_community":
                $show['category_list_info'] = $api->getCategoryList(I('get.cat_id'),null,null,null,null,I('get.userid'));
                $show['member_count'] = $api->getCommunityMemberCount(I('get.cat_id'));
                $show['is_ok'] = $api->checkUidCommunity(I('get.userid'),I('get.cat_id'));
                break;

            //获取关注数
            case "get_member_attention":
                $show['attention_count'] = $api->getMemberAttention(I('get.userid'));
                break;

            //获取粉丝数
            case "get_member_fans":
                $show['fans_count'] = $api->getMemberFans(I('get.userid'));
                break;

            //get adopt
            case "get_member_adopt":
                $show['adopt_count'] = $api->getMemberAdopt(I('get.userid'));
                break;

            //Cancel Attention
            case 'cancel_attention':
                $show['status'] = $api->cancelAttention(I('get.id'));
                break;


            //Invite Expert
            case 'my_invite_expert':
                $show['invite_expert_list'] = $api->getInviteExpert(I('get.'));
                break;

            case 'my_invite_expert_list':
                $my_invite_expert_list = $api->getMyInviteExpertList(I('get.userid'));
                foreach ($my_invite_expert_list AS $k=>$v){
                    $my_invite_expert_list[$k]['addtime'] = date('Y-m-d',$v['addtime']);
                    $my_invite_expert_list[$k]['content'] = msubstr($v['content'],0,20);
                }
                $show['my_invite_expert_list'] = $my_invite_expert_list;
                break;

            //点赞
            case 'set_agree':
                $show['status'] = $api->setAgree(I('get.to_uid'),I('get.status'),I('get.from_uid'),I('get.id'));
                if(I('get.status') == 1){
                    $api->addScore(I('get.to_uid'),'sa_agree_times');
                }
                break;

            //会员中心获取点赞数
            case 'get_answer_agree':
                $show['agree_count'] = $api->getAnswerAgree(I('get.userid'));
                break;

            //添加邀请专家
            case 'add_invite_message':
                $show['status'] = $api->addInviteExpert(I('get.'));
                break;

            //是否阅读
            case 'is_read':
                $info = I('get.');
                $show['status'] = $api->isRead($info);
                break;

            //判断是否有新版本
            case 'version':
                $oldver = I('get.ver');
                $show['ver'] = $api->version($oldver);
                break;

            //设置邀请码
            case 'set_apply_code':
                $show['status'] = $api->setApplyCode(I('get.userid'));
                break;

            //获取邀请码
            case 'get_apply_code':
                $show['code'] = $api->getApplyCode(I('get.userid'));
                break;

            //检查邀请码是否存在
            case 'check_apply_code':
                $show['code'] = $api->checkApplyCode(I('get.apply_code'));
                break;

            //获取我的邀请列表
            case 'get_my_apply_code':
                $show['my_apply_list'] = $api->getMyApplyCode(I('get.apply_code'));
                break;

            //获取我的关注 (个人信息展示)
            case 'get_user_attention':
                $show['count'] = count($api->getUserInfo(I('get.userid'),'attention'));
                break;

            //获取我的粉丝 (个人信息展示)
            case 'get_user_fans':
                $show['count'] = count($api->getUserInfo(I('get.userid'),'fans'));
                break;

            //获取我的粉丝 (个人信息展示)
            case 'get_user_ask':
                $show['count'] = count($api->getUserInfo(I('get.userid'),'fans'));
                break;

            //获取我的粉丝 (个人信息展示)
            case 'get_user_answer':
                $show['count'] = count($api->getUserInfo(I('get.userid'),'answer'));
                break;

            //获取同意 (个人信息展示)
            case 'agree_times':
                $show['count'] = count($api->getUserInfo(I('get.userid'),'agree_times'));
                break;

            //获取不同意 (个人信息展示)
            case 'against_times':
                $show['count'] = count($api->getUserInfo(I('get.userid'),'against_times'));
                break;

            //分享成功后增加积分
            case 'add_share_score':
                $show['member_profile'] = $api->getUserDetail(I('get.userid'),array('member_profile','expert_profile'));

                $last_time = $show['member_profile'][0]['last_login_time'];
                $now_time = time();
                $timestamp = $now_time - $last_time;
                if($timestamp > 86400){  //24h 后才可以累加积分
                    $api->addScore(I('get.userid'),'sa_share');
                }
                break;

            //获取会员积分
            case 'get_member_score':
                $show['score'] = $api->getMemberScore(I('get.userid'));
                break;

            //问题回复采纳
            case 'set_answer_adopt':
                $info = I('get.');
                $show['status'] = $api->setAnswerAdopt($info);
                break;

            //获取banner图列表
            case 'get_banner_list':
                $show['banner_list'] = $api->getBannerList();
                break;

            // 测试
            case 'test':
                $info['userid'] = 22;
                $info['askid'] = 5258;
                $result = $api->getQuestionAnswerStatus($info);
                break;

            default:
                $show['status'] = 201;
                break;
        }
        // 状态信息
        $show['msg'] = $api->status[$show['status']];
        $show_msgs = $jsoncallback . "(" . json_encode($show) . ")";
        //$show_msgs = json_encode($show);
        echo $show_msgs;
        exit();
    }

    //生成二维码
    public function qr_img(){
        //引入二维码类库
        require APP_ROOT.'./Application/Home/Api/PhpQrCode.php';

        $userid = I('get.userid');
        $value = Tools::think_encrypt($userid);
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 8;//生成图片大小
        \QRcode::png($value, 'qrcode.png', $errorCorrectionLevel, $matrixPointSize, 2);
        $logo = 'http://www.nongyao001.com/touchknow/logo.png';//准备好的logo图片
        $QR = 'qrcode.png';//已经生成的原始二维码图
        if ($logo !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 6;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                $logo_qr_height, $logo_width, $logo_height);
        }
        Header("Content-type: image/png");
        ImagePng($QR);
    }

    /**
     * 分享后读取内容
     */
    public function view_arc(){
        $api = new ApiAppKnow();
        $ask_id = I('ask_id');
        if(IS_GET){
            $ask_data = $api->getAskInfo($ask_id);
            $answer_data = $api->getAskAnswers($ask_id);
            $count = count($answer_data);
            $this->assign(['ask_data'=>$ask_data,'answer_data'=>$answer_data,'count'=>$count]);
        }
        $this->display();
    }
}