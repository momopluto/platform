<?php

/**
 * 微信接入验证
 * 在入口进行验证而不是放到框架里验证，主要是解决验证URL超时的问题
 */
if (! empty ( $_GET ['echostr'] ) && ! empty ( $_GET ["signature"] ) && ! empty ( $_GET ["nonce"] )) {
    $signature = $_GET ["signature"];
    $timestamp = $_GET ["timestamp"];
    $nonce = $_GET ["nonce"];

    $tmpArr = array (
        'huangxiaoji',
        $timestamp,
        $nonce
    );
    sort ( $tmpArr, SORT_STRING );
    $tmpStr = sha1 ( implode ( $tmpArr ) );

    if ($tmpStr == $signature) {
        echo $_GET ["echostr"];
    }
    exit ();
}


	define('APP_DEBUG', true);

	// // 绑定Client模块到当前入口文件
	// define('BIND_MODULE','Client');
	// define('BUILD_CONTROLLER_LIST','Index,User,Order');
	// define('BUILD_MODEL_LIST','User');

    define('APP_PATH', './Application/');
    define('THINK_PATH', './ThinkPHP/');

    // define('DOMAIN_URL', "http://momopluto.xicp.net");//服务器域名
    define('DOMAIN_URL', "http://127.0.0.1:8080");//服务器域名

    define('PUBLIC_URL', '/platform/Application/Public');//Public公共文件夹路径
    
    define('ADMIN_SRC', '/platform/Application/Admin/Source');//Admin资源文件夹路径
    define('HOME_SRC', '/platform/Application/Home/Source');//Home资源文件夹路径
    define('CLIENT_SRC', '/platform/Application/CLient/Source');//CLient资源文件夹路径
    

    define('TIME_0_OPEN','11:00');//中午开始营业时间
    define('TIME_0_CLOSE','14:00');//中午停止营业时间
    define('TIME_1_OPEN','17:00');//下午开始营业时间
    define('TIME_1_CLOSE','20:00');//下午停止营业时间
    define('OPENING_MSG', "餐厅营业时间：\n    中午：" . TIME_0_OPEN . "-" . TIME_0_CLOSE . "\n    下午：" . TIME_1_OPEN . "-" . TIME_1_CLOSE . "\n");
    define('ORDER_PHONE', "餐厅电话:15876502162\n华农短号:662162");

    require THINK_PATH.'ThinkPHP.php'

?>