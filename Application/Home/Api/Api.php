<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Api
 *
 * @author wodrow
 */
namespace Home\Api;

abstract class Api extends Model{

    public function __construct(){
        parent::__construct();
        $this->list_query('SET NAMES UTF8');
    }

    // 状态值=>状态信息
    public $status = array(
        '101'=>"未知异常",
        '200'=>"一切正常",
        '201'=>"参数异常",
        '202'=>"农医问药已存在该手机用户",
        '203'=>"注册失败",
        '204'=>"用户名或密码错误",
        '205'=>"该手机还未注册",
        '206'=>"验证码发送失败",
        '207'=>"验证码不正确",
        '208'=>"重置密码失败",
        '209'=>"已经发送过验证码，不能重复发送，请注意接收验证码",
        '210'=>"没有子地区了",
        '211'=>"手机用户信息异常",
        '212'=>"设置农医问药用户信息失败",
        '213'=>"图片没有上传成功",
        '214'=>"你还没有登陆",
        '215'=>"插入数据失败",
        '216'=>"未登录不能操作",
        '217'=>"数据已存在",
        '218'=>"参数错误",
        '219'=>"删除失败",
        '220'=>"更新失败",
        '221'=>"不能对自己操作",
    );

    /**
     * 获取所有地区
     * @return mixed
     */
    public function getAllArea(){
        $sql = "SELECT * FROM {$this->tables['area']}";
        $x = $this->list_query($sql);
        return $x;
    }

    /**
     * 根据地区id获取地区详细
     */
    public function getAreaFullNameFromAreaID($areaid){
        $y = $this->getAreaInfoFromAreaID($areaid,$x);
        return array_reverse($y);
    }
    private function getAreaInfoFromAreaID($areaid,&$areaInfo)
    {
        $tb_area = $this->tables['area'];
        $sql = "SELECT * FROM {$tb_area} WHERE areaid = {$areaid}";
        $x = $this->list_query($sql);

        if($x[0]['parentid'] == 0){
            $areaInfo[] = $x[0][areaname];
        }else{
            $areaInfo[] = $x[0][areaname];
            $this->getAreaInfoFromAreaID($x[0]['parentid'],$areaInfo);
        }
        return $areaInfo;
    }

    /**
     * 手机号码显示格式138****2057
     * @param $mobile
     * @return string
     */
    public function mobileHide($mobile){
        return substr($mobile,0,3)."****".substr($mobile,-4);
    }

}
