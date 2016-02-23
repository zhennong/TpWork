<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 8:40 AM
 */

return [
    //调试模式
    'LOG_RECORD'            =>  true,  // 进行日志记录
    'LOG_EXCEPTION_RECORD'  =>  true,    // 是否记录异常信息日志
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR,WARN,NOTIC,INFO,DEBUG,SQL',  // 允许记录的日志级别
    'DB_FIELDS_CACHE'       =>  false, // 字段缓存信息
    'DB_DEBUG'				=>  true, // 开启调试模式 记录SQL日志
    'TMPL_CACHE_ON'         =>  false,        // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_STRIP_SPACE'      =>  false,       // 是否去除模板文件里面的html空格与换行
    'SHOW_ERROR_MSG'        =>  true,    // 显示错误信息
    'URL_CASE_INSENSITIVE'  =>  false,  // URL区分大小写

    //trace
    'SHOW_PAGE_TRACE' =>true,

    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX' => 'app_', // 缓存前缀
    'DATA_CACHE_TYPE'   => 'File', // 数据缓存类型

    /* 数据库设置 */
    'DB_TYPE'               =>  'mysqli',     // 数据库类型
    'DB_HOST'               =>  '192.168.0.99', // 服务器地址
    'DB_NAME'               =>  'nongyao001_com',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'root',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'destoon_',    // 数据库表前缀

    //服务器ip
    'WEB_IP'=>'192.168.0.15',
    //网站url
    'WEB_URL'=>'',
    //网站域名
    'WEB_DOMAIN'=>'',

    'MODULE_ALLOW_LIST' => array('Home','Admin','Common'),

    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => [
        '__STATIC__' => __ROOT__ . '/Public/static/',
        '__COMMON__' => __ROOT__ . '/Public/Common/',
        '__HOME__' => __ROOT__ . '/Public/Home/',
        '__ADMIN__' => __ROOT__ . '/Public/Admin/',
        '__UPLOADS__' => __ROOT__.'/Uploads/',
        '__RUNTIME__' => RUNTIME_PATH,
        '__MALL_URL__' => 'http://www.nongyao001.com/',
    ],

    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => 'app_session_', //session前缀
    'COOKIE_PREFIX'  => 'app_cookie_', // Cookie前缀 避免冲突

    //全局过滤函数
    'DEFAULT_FILTER' => 'htmlspecialchars',

    'URL_MODEL'             =>  2,

    // 其他数据库
//    'BASIC_LIB_TABLES' => 'mysql://wft_open:WeiFengTou2015@116.255.217.235:3306/basic_lib_tables',

    //上传二级目录
    'UPLOAD2DIR' => 'wodrow/',
];