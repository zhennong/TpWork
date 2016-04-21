<?php
namespace Admin\Controller;

use Common\Tools;

class UpgradeController extends AuthController {
    public function softSetting(){
        //判断是否编辑xml
        if(I('get.action') == 'edit'){
            $softname = I('get.softname');
            $softversion = I('get.softversion');
            $softfilename = I('get.softfilename');
            $softsize = I('get.softsize');

            $dom = new \DOMDocument();
            $dom->load($_SERVER['DOCUMENT_ROOT'].'/touchknow/upgrade.xml');
            $file = $dom->getElementsByTagName("file");
            $file->item(0)->setAttribute("name",$softname);

            $news = $dom->getElementsByTagName("news");
            $news->item(0)->setAttribute("version",$softversion);
            $news->item(0)->setAttribute("android_filename",$softfilename);

            $size = $dom->getElementsByTagName("size");
            $size->item(0)->setAttribute("android",$softsize);

            $result = $dom->save($_SERVER['DOCUMENT_ROOT'].'/touchknow/upgrade.xml');
            if($result !== false){
                $this->ajaxReturn(200);
            }
        }

        $info = simplexml_load_file($_SERVER['DOCUMENT_ROOT'].'/touchknow/upgrade.xml');
        $iphone_filename=$info->news['iphone_filename']; //iphone下载文件
        $android_filename=$info->news['android_filename']; //androiad下载文件
        $iphone_size=$info->size['iphone'];//iphone文件大小.
        //$android_size=$info->size['android'];//andoird文件大小.
        $version=$info->news['version']; //版本号
        $updateFileName=$info->file['name'];//客户端名称.

        //自动抓取文件大小
        $software = Tools::getFileSize("http://www.nongyao001.com/file/nywy.apk");

        $this->assign(['iphone_filename'=>$iphone_filename,'android_filename'=>$android_filename,'iphone_size'=>$iphone_size,'android_size'=>$software,'version'=>$version,'name'=>$updateFileName]);
        $this->display();
    }
}