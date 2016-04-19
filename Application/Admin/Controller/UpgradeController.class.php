<?php
namespace Admin\Controller;

use Common\Tools;

class UpgradeController extends AuthController {
    public function softSetting(){
        //判断是否编辑xml
        if(I('get.action') == 'edit'){
            dump(2);
        }

        $info = simplexml_load_file($_SERVER['DOCUMENT_ROOT'].'/touchknow/upgrade.xml');
        $iphone_filename=$info->news['iphone_filename']; //iphone下载文件
        $android_filename=$info->news['android_filename']; //androiad下载文件
        $iphone_size=$info->size['iphone'];//iphone文件大小.
        $android_size=$info->size['android'];//andoird文件大小.
        $version=$info->news['version']; //版本号
        $updateFileName=$info->file['name'];//客户端名称.

        $this->assign(['iphone_filename'=>$iphone_filename,'android_filename'=>$android_filename,'iphone_size'=>$iphone_size,'android_size'=>$android_size,'version'=>$version,'name'=>$updateFileName]);
        $this->display();
    }
}