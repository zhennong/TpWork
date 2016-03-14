<?php
namespace Home\Controller;

header('Access-Control-Allow-Origin: *');

use Common\Tools;
use Think\Controller;
use Home\Common\ApiAppKnow;



class IndexController extends Controller {

    //农医问药接口
    public function index(){
        // 返回函数
        $jsoncallback = I('get.jsoncallback');
        $api = new ApiAppKnow();

        $show['status'] = 200;

        switch (I('get.action')) {
            // 登陆
            case 'login':
                $password = md5(md5(I('get.password')));
                $x = $api->getUserFromMobile(I('get.mobile'));
                $show['member_info'] = $x;
                if ($x[0]['password'] == $password) {
                    $show['member_info'] = $x;
                    $api->getLastLoginTime($x[0]['userid']);
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
                        S($k, $x, 24 * 3600);
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
                        }
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
                        $x = rand(100000, 999999);
                        S($k, $x, 24 * 3600);
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

                //登录积分设置 每天累加一分
                $last_time = date('Y-m-d',$show['member_profile'][0]['last_login_time']);
                $now_time = date('Y-m-d',time());
                if($now_time != $last_time){
                    $api->addScore(I('get.userid'),'sa_login');
                }
                break;

            // 设置个人信息
            case 'set_member_profile':
                $info = I('get.');
                $x = $api->setAppknowMemberProfile($info);
                if (!$x) {
                    $show['status'] = 212;
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
                        if (count(Tools::str2arr(S($k1))) < 6) {
                            if (!S($k1)) {
                                S($k1, $ask_img_path, 3600 * 24);
                            } else {
                                $x = S($k1);
                                $x = $x . "," . $ask_img_path;
                                S($k1, $x, 3600 * 24);
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
                if ($api->addQuickAsk($info)) {
                    $k = "user_ask_image_" . I('get.userid');
                    $k1 = $k . "_ask_tmp_image";
                    S($k1, null);
                    //提交问题积分设置
                    $api->addScore(I('get.userid'),'sa_login');

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
                $x = $api->getAskList(I('get.start'), I('get.limit'), I('get.cat_id'));
                foreach ($x as $k => $v) {
                    $x[$k]['addtime_date'] = date("Y-m-d", $v['addtime']);
                    for ($i = 0; $i < 6; $i++) {
                        if ($v['thumb' . $i]) {
                            $x[$k]['ask_images'][] = $v['thumb' . $i];
                            $x[$k]['image_count'] = $i + 1;
                        }
                    }
                    $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),'');
                }
                $show['ask_list'] = $x;
                break;

            // 获取问题详情
            case "get_ask_info":
                $x = $api->getAskInfo(I('get.askid'));
                foreach ($x as $k => $v) {
                    $x[$k]['addtime_date'] = date("Y-m-d", $v['addtime']);
                    for ($i = 0; $i < 6; $i++) {
                        if ($v['thumb' . $i]) {
                            $x[$k]['ask_images'][] = $v['thumb' . $i];
                            $x[$k]['image_count'] = $i + 1;
                        }
                    }
                    $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),'');
                }
                $show['ask_info'] = $x;
                $x = $api->getAskAnswers(I('get.askid'));
                foreach ($x as $k => $v) {
                    $x[$k]['addtime_date'] = date("Y-m-d", $v['addtime']);
                    $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),'');
                }
                $show['question_answers'] = $x;
                break;

            // 我要回答
            case "submit_questions_answer":
                if ($api->addQuestionAnswer(I('get.'))) {
                    //回答问题积分设置
                    $api->addScore(I('get.userid'),'sa_login');
                } else {
                    $show['status'] = 215;
                }
                break;

            // 意见反馈（2016.02.16）
            case 'submit_feed_back':
                if (!$api->addFeedBack(I('get.'))) {
                    $show['status'] = 215;
                }
                break;

            // 上传身份证正面照（2016.02.16）
            case 'upload_id_card_front':
                if (!I('get.userid')) {
                    $show['status'] = 213;
                } else {
                    $_dir = APP_ROOT.C('UPLOADS').'card_front/';
                    $extend_type = Tools::get_extend($_FILES['filename']['name']);
                    $upload_dir = DT_ROOT . $_dir;
                    $upload_name = "user_" . I('get.userid') . "_" . date("Y_m_d__H_i_s", time());
                    $show['status'] = ApiAppKnow::uploadImage($_FILES['filename'], $upload_dir, $upload_name);
                    if ($show['status'] == 200) {
                        $info = array('card_front_path' => $_dir . $upload_name . "." . $extend_type);
                        echo $show['save_path'] = $info['card_front_path'];
                        exit();
                    } else {
                        $show['status'] = 101;
                    }
                }
                break;

            // 上传身份证反面照（2016.02.16）
            case 'upload_id_card_back':
                if (!I('get.userid')) {
                    $show['status'] = 213;
                } else {
                    $_dir = APP_ROOT.C('UPLOADS').'card_back/';
                    $extend_type = Tools::get_extend($_FILES['filename']['name']);
                    $upload_dir = DT_ROOT . $_dir;
                    $upload_name = "user_" . I('get.userid') . "_" . date("Y_m_d__H_i_s", time());
                    $show['status'] = ApiAppKnow::uploadImage($_FILES['filename'], $upload_dir, $upload_name);
                    if ($show['status'] == 200) {
                        $info = array('card_back_path' => $_dir . $upload_name . "." . $extend_type);
                        echo $show['save_path'] = $info['card_back_path'];
                        exit();
                    } else {
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

            //加入收藏
            case 'add_favourite':
                $show['status'] = $api->addFavourite(I('get.'));
                break;

            //显示收藏
            case 'get_favourite_list':
                $show['favourite_info'] = $api->getFavourite(I('get.userid'), I('get.type'));
                break;

            case 'my_ask_history':
                $show['ask_historys'] = $api->myAskHistory(I('get.userid'));
                break;

            //问答历史
            case 'my_answer_history':
                $show['answer_historys'] = $api->myAnswerHistory(I('get.userid'));
                break;

            //添加关注
            case 'add_attention':
                $info = $_GET;
                if(I('get.type') == 'scanner'){
                    $info['attention_uid'] = Tools::think_decrypt(I('get.attention_uid'));
                }
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

            //获取分类
            case 'get_category_list':
                $show['category_list_info'] = $api->getCategoryList(I('get.id'), I('get.pid'),null,null,1);
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
                $show['my_category_list'] = $api->getMyCategoryList(I('get.userid'));
                break;

            //获取圈子信息
            case "get_community":
                $show['category_list_info'] = $api->getCategoryList(I('get.cat_id'));
                $show['member_count'] = $api->getCommunityMemberCount(I('get.cat_id'));
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
                $show['invite_expert_list'] = $api->getInviteExpert();
                break;

            //点赞
            case 'set_agree':
                $show['status'] = $api->setAgree(I('get.userid'),I('get.status'));
                break;

            // 测试
            case 'test':
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
        include '\Application\Home\PhpQrCode.class.php';

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
}