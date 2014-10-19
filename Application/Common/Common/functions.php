<?php
/**
 * 公共方法函数
 *
 */

// 格式化打印数组
function p($array){
    dump($array, 1, '<pre>', 0);
}

// 获取本月的第1天和最后1天
function getMonth_StartAndEnd($date){
    $firstday = date("Y-m-01",strtotime($date));
    $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
    // return array($firstday,$lastday);//返回日期
    return array(strtotime($firstday),strtotime($lastday));//返回时间戳
 }

// 获取上月的第1天和最后1天
function getlastMonth_StartAndEnd($date){
    $timestamp=strtotime($date);
    $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
    $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
    // return array($firstday,$lastday);//返回日期
    return array(strtotime($firstday),strtotime($lastday));//返回时间戳
 }

// 获取下月的第1天和最后1天
function getNextMonth_StartAndEnd($date){
    $timestamp=strtotime($date);
    $arr=getdate($timestamp);
    if($arr['mon'] == 12){
        $year=$arr['year'] +1;
        $month=$arr['mon'] -11;
        $firstday=$year.'-0'.$month.'-01';
        $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
    }else{
        $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)+1).'-01'));
        $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
    }
    // return array($firstday,$lastday);//返回日期
    return array(strtotime($firstday),strtotime($lastday));//返回时间戳
}


/*========================================送餐队长接口 begin===================================*/
//订单信息detail，用于接口
//格式：
//小黄焖鸡米饭 1份/大黄焖鸡米饭 2份
function _detail($an_item){
    $menu_lists = array(//数据库对应的菜式
        'info_1' => '小黄焖鸡米饭',
        'info_2' => '辣小黄焖鸡米饭',
        'info_3' => '大黄焖鸡米饭',
        'info_4' => '辣大黄焖鸡米饭',
    );
    // p($menu_lists);
    $detail = "";
    foreach ($menu_lists as $key => $value) {
        if($an_item[$key] != 0){
            if($detail == ""){
                $detail = $value." ".$an_item[$key]."份";    
            }else{
                $detail .= "/".$value." ".$an_item[$key]."份";    
            }            
        }
    }

    return $detail;
}

function send_order_to_captains($an_item){
    $detail = _detail($an_item);

    //中文字符要urlencode
    $url_get = "http://203.195.152.141:3000/itemadd?total=" . urlencode($an_item['total']) . "&phoneUser=" . urlencode($an_item['phone']) . "&address=" . urlencode($an_item['address']) . "&detail=" . urlencode($detail) . "&idPlatform=" . urlencode($an_item['id']) . "&phoneShop=15876502162" . "&platform=weixin";
    
    $errtxt = _http_get($url_get);

    return json_decode ( $errtxt, true );
}
/*=========================================送餐队长接口 end======================================*/
?>