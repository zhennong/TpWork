<?php
header("Content-type: text/xml");
$apps_url="http://www.nongyao001.com/file/";
$oldver = $_REQUEST['ver'];//客户端传过来的版本号
$platform = $_REQUEST['platform'];//客户端的平台
$info = simplexml_load_file($_SERVER['DOCUMENT_ROOT'].'/others/touchknow/upgrade.xml');

$iphone_filename=$info->news['iphone_filename']; //iphone下载文件
$android_filename=$info->news['android_filename']; //androiad下载文件
$iphone_size=$info->size['iphone'];//iphone文件大小.
$android_size=$info->size['android'];//andoird文件大小.
$version=$info->news['version']; //版本号
$updateFileName=$info->file['name'];//客户端名称.

if ($version > $oldver)
{
    switch ($platform)
    {
        case "0"://iphone 暂不支持
            /*echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
            echo "<results>";
            echo "<updateFileName>".$updateFileName."</updateFileName>";
            echo "<updateFileUrl>".$server_domain.$apps_dir.$iphone_filename."</updateFileUrl>";
            echo "<fileSize>".$iphone_size."</fileSize>";
            echo "<version>".$version."</version>";
            echo "</results>";*/
            break;
        case "1"://android
            echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
            echo "<results>";
            echo "<updateFileName>".$updateFileName."</updateFileName>";//客户端名字
            echo "<updateFileUrl>".$apps_url.$android_filename."</updateFileUrl>";//返回给客户端的下载地址
            echo "<fileSize>".$android_size."</fileSize>";//文件大小
            echo "<version>".$version."</version>";//版本信息
            echo "</results>";
            break;
    }
}