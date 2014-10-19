<?php
/**
 * Home公共函数
 *
 */

// 检验Home是否已登录
function is_login(){
    if(session('?login_flag') && session('login_flag')){
        return true;
    }
    return false;
}

/**
 * GET 请求，curl函数
 *
 * @param string $url 请求的网址
 * @return string $sContent 返回的内容
 */
function _http_get($url) {
    $oCurl = curl_init ();
    if (stripos ( $url, "https://" ) !== FALSE) {
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
    }
    curl_setopt ( $oCurl, CURLOPT_URL, $url );
    curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec ( $oCurl );
    $aStatus = curl_getinfo ( $oCurl );
    curl_close ( $oCurl );
    if (intval ( $aStatus ["http_code"] ) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

/**
 * POST 请求，curl函数
 *
 * @param string $url 请求的网址
 * @param string $strPOST  post传递的数据 
 * @return string sContent 返回的内容
 */
function _http_post($url, $strPOST) {
    $oCurl = curl_init ();
    if (stripos ( $url, "https://" ) !== FALSE) {
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
    }

    curl_setopt ( $oCurl, CURLOPT_URL, $url );
    curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $oCurl, CURLOPT_POST, true );
    curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
    $sContent = curl_exec ( $oCurl );
    $aStatus = curl_getinfo ( $oCurl );
    curl_close ( $oCurl );
    if (intval ( $aStatus ["http_code"] ) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

//获得access_token，判断有无缓存
function get_access_token($fromUsername){
    $map ['token'] = $fromUsername;

    $value = S($map ['token']);// 读取缓存
    // echo $value;
    // S($map ['token'],null);
    if(!$value){
        // echo $value."过期啦or不存在！";die;
        $info = M ( 'public' )->where ( $map )->find ();
        $url_get = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $info ['appid'] . '&secret=' . $info ['secret'];

        // $timeout = 5;
        // curl_setopt ( $ch1, CURLOPT_CONNECTTIMEOUT, $timeout );

        $accesstxt = _http_get($url_get);

        $access = json_decode ( $accesstxt, true );
        if (empty ( $access ['access_token'] )) {
            // $this->error ( '获取access_token失败,请确认AppId和Secret配置是否正确,然后再重试。' );
        }
        // 采用文件方式缓存数据300秒
        S($map ['token'],$access ['access_token'],array('type'=>'file','expire'=>7200));
        $value = S($map ['token']);// 读取缓存
    }

    return $value;
}

/**
 * 向用户发送信息（文本），服务号才能用
 *
 * @param $fromUsername 发送者token
 * @param $toUsername 接收者openid
 * @param $contentStr 发送的内容
 * @return mixed 结果代码
 */
function send_msg($fromUsername,$toUsername,$contentStr){
    //获取access_token,判断有无缓存
    $access_token = get_access_token($fromUsername);

    $contentStr = urlencode($contentStr);
    $a = array("content" => $contentStr);
    $b = array("touser" => $toUsername, "msgtype" => "text", "text" => $a);
    $strPOST = json_encode($b);
    $strPOST = urldecode($strPOST);
    $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $access_token;

    $res = _http_post($url, $strPOST);

    return json_decode ( $res, true );
}

?>