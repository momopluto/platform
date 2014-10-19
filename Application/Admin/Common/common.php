<?php
/**
 * Admin公共函数
 *
 */

// 检验Admin是否已登录
function is_admin_login(){
    if(session('?admin_login_flag') && session('admin_login_flag')){
        return true;
    }
    return false;
}

// 检验是否已设置公众号(即是否有token)
function has_token(){
    if(session('?token') && session('token') != null){
        return true;
    }
    return false;
}

/*======================================微信操作方法 begin==================================*/

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

// 记录微信操作日志
function addWeixinLog($data, $data_post = '') {
    $log ['cTime'] = time ();
    $log ['cTime_format'] = date ( 'Y-m-d H:i:s', $log ['cTime'] );
    $log ['data'] = is_array ( $data ) ? serialize ( $data ) : $data;
    $log ['data_post'] = $data_post;
    M ( 'weixin_log' )->add ( $log );
}

// 获取当前用户的OpenId
function get_openid($openid = NULL) {
    if ($openid !== NULL) {
        session ( 'openid', $openid );
    } elseif (! empty ( $_REQUEST ['openid'] )) {
        session ( 'openid', $_REQUEST ['openid'] );
    }
    $openid = session ( 'openid' );

    if (empty ( $openid )) {
        return - 1;
    }

    return $openid;
}

// 获取当前用户的Token
function get_token($token = NULL) {
    if ($token !== NULL) {
        session ( 'token', $token );
    } elseif (! empty ( $_REQUEST ['token'] )) {
        session ( 'token', $_REQUEST ['token'] );
    }
    $token = session ( 'token' );

    if (empty ( $token )) {
        return - 1;
    }

    return $token;
}

//获得access_token，判断有无缓存
function get_access_token(){
    $map ['token'] = get_token ();

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
            $this->error ( '获取access_token失败,请确认AppId和Secret配置是否正确,然后再重试。' );
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
 * @param $toUsername 接收者openid
 * @param $contentStr 发送的内容
 * @return mixed 结果代码
 */
function send_msg($toUsername,$contentStr){
    //获取access_token,判断有无缓存
    $access_token = get_access_token();

    $contentStr = urlencode($contentStr);
    $a = array("content" => $contentStr);
    $b = array("touser" => $toUsername, "msgtype" => "text", "text" => $a);
    $strPOST = json_encode($b);
    $strPOST = urldecode($strPOST);
    $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $access_token;

    $res = _http_post($url, $strPOST);

    return json_decode ( $res, true );
}

// 通过openid获取微信用户基本信息,此功能只有认证的服务号才能用
function getWeixinUserInfo($openid, $token) {
    if (empty ( $GLOBALS ['user'] ['appid'] )) {
        return false;
    }
    $param ['appid'] = $GLOBALS ['user'] ['appid'];
    $param ['secret'] = $GLOBALS ['user'] ['secret'];
    $param ['grant_type'] = 'client_credential';

    $url = 'https://api.weixin.qq.com/cgi-bin/token?' . http_build_query ( $param );
    $content = file_get_contents ( $url );
    $content = json_decode ( $content, true );

    $param2 ['access_token'] = $content ['access_token'];
    $param2 ['openid'] = $openid;
    $param2 ['lang'] = 'zh_CN';

    $url = 'https://api.weixin.qq.com/cgi-bin/user/info?' . http_build_query ( $param2 );
    $content = file_get_contents ( $url );
    $content = json_decode ( $content, true );
    return $content;
}

// 关键字匹配
function match_keyword($keyword){

    if(trim($keyword)){
        $map['token'] = get_token();

        //完全匹配
        $map['keyword_type'] = 0;
        $map['keyword'] = array('like','% '.$keyword.' %');

        $result = M('keyword')->where($map)->order('sort')->limit(1)->select();
        //p($result);
        if($result){
            //echo 完全;
            return $result[0];
        }else{
            //模糊匹配
            $map['keyword_type'] = 1;
            $map['keyword'] = array('like',"%".$keyword."%");
            $result = M('keyword')->where($map)->order('sort')->limit(1)->select();
            if($result){
                //echo 模糊;
                return $result[0];
            }else{
                return false;
            }
        }
    }
}
/*=========================================微信操作方法 end====================================*/





/*=========================================黄小吉操作方法 begin====================================*/

// 卖萌回复(文本)
function playing_cute(){
    $now  = date("H:i");            
    if((strtotime(TIME_0_OPEN) <= strtotime($now) && strtotime($now) < strtotime(TIME_0_CLOSE)) 
        || (strtotime(TIME_1_OPEN) <= strtotime($now) && strtotime($now) < strtotime(TIME_1_CLOSE))){
        $result_text = "嗯嗯！\n现在是营业时间，小吉正在认真地工作[奋斗]可能没那么快回复哦^皿^\n想快速调戏我就下单吧，接收订单还是很快的哟~[愉快]\n";
    }else{
        $result_text = "Hello~! 现在不是营业时间，猜猜小吉在干嘛？[调皮]";
    }
    return $result_text;
}

// 用户下单时数据库信息处理，返回文本信息
/**
 * @param $current_sel 选择的菜式
 * @param $current_op 操作+/-
 * @return string
 */
function menu_info_handle($current_sel, $current_op){
    $menu_sql = array(//菜单对应数据库表
        '小黄焖鸡米饭' => 'info_1',
        '辣小黄焖鸡米饭' => 'info_2',
        '大黄焖鸡米饭' =>'info_3',
        '辣大黄焖鸡米饭' => 'info_4',
    );
    $price_lists = array(//菜单价钱
        '小黄焖鸡米饭' => 12,
        '辣小黄焖鸡米饭' => 12,
        '大黄焖鸡米饭' =>20,
        '辣大黄焖鸡米饭' => 20,
    );
    //1.如果用户未订过餐，即数据库无其openid信息，首先添加openid，转2
    //          用户已参加过订餐，转2
    //2.用$current_sel和$current_op更新数据库，$text增加成功信息，转3；否则，失败则$text=错误信息，返回给用户，return
    //3.循环遍历数据库，将当前订单信息转换成$text，统计总价
    //4.$text末尾增加“选餐完毕请操作：右下角“订单”->提交订单”
    //5.成功则返回$text；否则，失败则$text=错误信息，返回给用户，return

    $param ['token'] = get_token ();
    $param ['openid'] = get_openid ();

    $model = M('orderman');
    if(!$model->where($param)->count()){//数据库未有该openid
        $model->add($param);//添加该订餐人信息
    }

    if(strcmp($current_op,'+') == 0){
        $model->where($param)->setInc($menu_sql[$current_sel]);
        $model->where($param)->setField('status', 1);
        $text = $current_sel.' '.$current_op."1 成功！\n\n";
    }else{
        $temp = $model->where($param)->find();
        if($temp[$menu_sql[$current_sel]] > 0){
            $model->where($param)->setDec($menu_sql[$current_sel]);
            $text = $current_sel.' '.$current_op."1 成功！\n\n";
        }else{
            $text = $current_sel.' '.$current_op."1 失败！！\n\n没有此选单！！";
            return $text;
        }
    }
    //数据库订单信息处理
    $text .= "当前选单:***************\n";
    $total = 0;//总价
    $info_count = 0;//总份数
    $info_text = '';//订单文本信息
    $one = $model->where($param)->find();
    foreach($menu_sql as $key => $value){
        if($one[$value] != 0){
            $total += $price_lists[$key] * $one[$value];
            $info_count += $one[$value];
            $info_text .= "-".$key ."/￥".$price_lists[$key]."   x ". $one[$value]."\n";
        }
    }
    $info_text .= "\n->总计: ".$info_count."份，".$total."元\n";
    $text .= $info_text;
//        $info_text = '<pre>'.$info_text.'</pre>';
    $tt = array('total' => $total, 'info_count' => $info_count, 'info_text' => $info_text);
    $model->where($param)->setField($tt);

    $text .= "****************************\n\n";
    $text .= "选餐完毕请操作:\n";
    $text .= "  右下角\"订单\"->提交订单";

    if($total == 0){
        $model->where($param)->setField('status', 0);
        $text = "当前选单为空！";
    }

    return $text;
}

/**
 * 菜单“提交订单”
 *
 * @param $param token和openid
 * @return string/array 文本或图文
 */
function menu_submit_order($param){
    $man_model = M('orderman');
    $one = $man_model->where($param)->find();
    if(!$one['status']){//没有订单
        $result_text = "未有选单！请确认！";
        return $result_text;
    }

    //组合多图文信息
    $articles [0] = array (//------------------确认下单
        'Title' => "【确认下单】",
        'Description' => '无',
        'PicUrl' => DOMAIN_URL."/think/Huangxiaoji/Source/Image/12345.jpg",
        'Url' => DOMAIN_URL.U("Home/Client/submit_order")."?openid=".$param['openid']."&token=".$param['token'],
    );

    if($one['receiver_phone'] != null && $one['takeout_address'] != null){
        $INFO = "手机号：".$one['receiver_phone']."\n送餐地址：".$one['takeout_address'];
    }else{
        $INFO ="您尚未绑定[手机号+送餐地址]";
    }
    $articles [1] = array (//-------------------手机号+送餐地址
        'Title' => "【送餐信息】\n".$INFO."\n\n--------------------->#点此编辑#",
        'Description' => '',
        'PicUrl' => '',
        'Url' => DOMAIN_URL.U("Home/Client/bind_info")."?openid=".$param['openid']."&token=".$param['token']."&source=order",
    );

    $articles [2] = array (//-----------------------------订单信息
        'Title' => "【订单信息】\n".$one['info_text'],
        'Description' => '',
        'PicUrl' => '',
        'Url' => '',
    );
    return $articles;
}

/**
 * 菜单“清空选单”
 *
 * @param $param token和openid
 * @return string 文本
 */
function menu_clear_order($param){
    $man_model = M('orderman');
    $one = $man_model->where($param)->find();
    if(!$one['status']){
        $result_text = "当前选单为空！";
        return $result_text;
    }

    $t = array(
        'info_1' => 0,
        'info_2' => 0,
        'info_3' => 0,
        'info_4' => 0,
        'total' => 0,
        'status' => 0,
    );
    if($man_model->where($param)->setField($t)){
        $result_text = "清空选单成功！";
        return $result_text;
    }
}

/**
 * 菜单 “催单”
 *
 * @param $param token和openid
 * @return string 文本
 */
function menu_urge_order($param){
    //1.查询该用户是否有未完成的订单（即“完成”和“取消”的订单除外）、
    //  没有，返回没有订单
    //  有，2.判断是否处于营业时间
    //      不是，返回营业时间，提醒耐心等待
    //      是，3.改变订单状态标记status=4，催单状态
    //          设置成功，我们将尽快响应
    //          失败，返回系统正忙
    $item_model = M('orderitem');
    $count = $item_model->where($param)->where("status!=2 and status!=3")->count();
    // $this->replyText ($count);
    if($count){//步骤1
        $now  = date("H:i");

        if((strtotime(TIME_0_OPEN) <= strtotime($now) && strtotime($now) < strtotime(TIME_0_CLOSE))
            || (strtotime(TIME_1_OPEN) <= strtotime($now) && strtotime($now) < strtotime(TIME_1_CLOSE))){//步骤2
            //送餐时间，允许催单

            if($item_model->where($param)->where("status!=2 and status!=3")->setField('status', 4)){
                $result_text = "催单成功！\n我们将尽快响应！";
            }else{
                $result_text = "系统正忙！请稍候再试！";
            }
        }else{
            $result_text = OPENING_MSG;
            $result_text .= "------\n";
            $result_text .= ORDER_PHONE;
            $result_text .= "\n\n目前餐厅尚未开始营业，请稍后再催单，谢谢！";
        }

    }else{
        $result_text = "未有选单！请确认！";
    }
    return $result_text;
}

/**
 * 菜单“绑定信息”
 *
 * @param $param token和openid
 * @return mixed 图文
 */
function menu_bind_info($param){
    // 单条图文回复
    $man_model = M('orderman');
    $one = $man_model->where($param)->find();
    if($one['receiver_phone'] != null && $one['takeout_address'] != null){
        $des2 = "点击修改信息";
        $INFO = "手机号:".$one['receiver_phone']."\n送餐地址:".$one['takeout_address'];
    }else{
        $des2 = "点击绑定信息";
        $INFO ="您尚未绑定[手机号+送餐地址]";
    }

    $articles [0] = array (
        'Title' => $des2,
        'Description' => $INFO,
        'PicUrl' => DOMAIN_URL."/think/Huangxiaoji/Source/Image/12345.jpg",//文件名不能是中文
        'Url' => DOMAIN_URL.U("Home/Client/bind_info")."?openid=".$param['openid']."&token=".$param['token'],
    );
    return $articles;
}

function menu_myOrders($param){
    //按id desc,status顺序查找用该用户token和openid匹配的订单
    //   无，则回暂无订单
    //   有，则组合成文本信息输出
    //      返回文本信息
    $item_model = M('orderitem');
    $items = $item_model->where($param)->order('id desc,status')->limit(5)->select();
    $result_text ='';
    if($items){
        $key = 1;
        foreach($items as $an_item){
            $temp_text = "$key.\n下单时间: ".date('m-d H:i',$an_item['cTime'])."\n";
            $temp_text .= "订单信息:\n".$an_item['info_text']."\n";
            $temp_text .= "联系手机: ".$an_item['phone']."\n";
            $temp_text .= "送餐地址: ".$an_item['address']."\n";

            if($an_item['status'] == 0){
                $status = "餐厅未确认";
            }elseif($an_item['status'] == 1){
                $status = "餐厅已确认";
            }elseif($an_item['status'] == 2){
                $status = "订单完成";
            }elseif($an_item['status'] == 4){
                $status = "催促订单";
            }elseif($an_item['status'] == 5){
                $status = "已响应催单";
            }else{//$an_item['status'] == 3
                $status = "订单取消[".$an_item['note']."]";
            }
            $temp_text .= "状态: ".$status;

            if($key != 1){
                $temp_text .= "\n----------------------------\n";
            }
            $result_text = $temp_text.$result_text;
            $key++;
        }
        $result_text .= "\n*********************\n".ORDER_PHONE;
    }else{
        $result_text = "暂无订单！";
    }
    return $result_text;
}

// 联系黄小吉
function contact_hxj(){
    $result_text = OPENING_MSG;
    $result_text .= "------\n";
    $result_text .= ORDER_PHONE;

    return $result_text;
}

/*=========================================黄小吉操作方法 end====================================*/

?>