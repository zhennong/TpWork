<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
if(version_compare(PHP_VERSION,'5.4.0','<'))  die('require PHP > 5.4.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

// 定义应用根目录
define('APP_ROOT',dirname(__FILE__));

// 定义默认模块
define('BIND_MODULE','Admin');

// 定义应用目录
define('APP_PATH',APP_ROOT.'/Application/');

// 定义缓存目录
define('RUNTIME_PATH',APP_ROOT.'/Runtime/');

// composer
require 'vendor/autoload.php';

// vendor目录
define('VENDOR_PATH',APP_ROOT.'/vendor/');

//设置自定义类自动加载
spl_autoload_register(function($classname){
    $_file = './Application/' . str_replace('\\','/',$classname) . '.php';
    if(file_exists($_file)){
        require_once $_file;
    }
});

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
