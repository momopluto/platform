<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 订单管理
 * 
 */
class OrderitemController extends HomeController {

    function _initialize() {
        parent::_initialize ();

        /* 限制一定要设置了餐厅信息才能进行其它操作 */
        $rid = 10086 + session('uid');
        has_rstInfo($rid) || $this->error('您未初始化设置餐厅信息，请先设置！', U('Shopmanage/set'));
        
        $this->model = M ('orderitem', ' ');
    }

    // 菜单列表
    function lists(){
        $rid = 10086 + session('uid');

    	if(IS_POST){
//TODO，此处貌似不用post
//TODO，此处数据库访问次数非常多，效率成问题，须改！！！！
    		
    	}else{

            if(I('get.date')){//是查询，能get到date
                $the_day = I('get.date');                
            }else{//不是，则默认为今天
                $the_day = date('Y-m-d');
            }
            // echo $the_day;die;

            $map['rid'] = array('eq',$rid);
            $map['status'] = array('neq', 0);
            $orders = $this->model->where($map)->where("DATE_FORMAT(FROM_UNIXTIME(cTime),'%Y-%m-%d')='" . $the_day . "'")->select();
            // 分页
            $count = count($orders);
            // $count = $this->model->where("rid = $rid"." AND status != 0"." AND DATE_FORMAT(FROM_UNIXTIME(cTime),'%Y-%m-%d')='" . $the_day . "'")->count();// 查询订单(已确认、有效或无效订单)
            // echo $count;
            // p($this->model);die;


            $Page = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(10)
            // $Page->setConfig('theme', '<li><a>%totalRow% %header%</a></li> %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end% ');
            $show = $Page->show();// 分页显示输出
            
            $rid = intVal(10086 + session('uid'));

            //查询日期为$the_day的订单数
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $data = $this->model->where($map)->where("DATE_FORMAT(FROM_UNIXTIME(cTime),'%Y-%m-%d')='" . $the_day . "'")->order('today_sort desc')->limit($Page->firstRow.','.$Page->listRows)->select();// 订单信息(已确认、有效或无效订单)

            // p($data);die;

            foreach ($data as $key => $value) {
                $data[$key]['order_info'] = json_decode($data[$key]['order_info'], true);
            }

            $total = 0;//有效总金额
            $count_1 = 0;//有效订单数
            foreach ($orders as $an_order) {
                if($an_order['status'] != 3){
                    $total += $an_order['total'];
                    $count_1 ++;
                }
            }

            // echo $count_1;die;
            $this->assign('all_count' , $count);//订单总数
            $this->assign('total' , $total);//订单总金额
            $this->assign('count_1' , $count_1);//有效订单数
            
            $this->assign('the_day' , $the_day);//选择的日期
            $this->assign('list',$data);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
            $this->assign('data',$data);


            $this->display();
    	}
    	
    }
   

    // 确认订单
    function confirm(){
        // p(I('post.'));die;
        $guid = I('get.guid');

        $an_item = $this->model->where("guid = $guid")->find();
        // p($an_item);die;
        
        if(is_null($an_item['rTime'])){//未确认订单
            $update['rTime'] = NOW_TIME;
        }

        $update['status'] = 1;
        if($this->model->where("guid = $guid")->setField($update)){//更新订单信息

            //判断是否有token和openid（即判断是否已经关注服务号）
            if(!is_null($an_item['openid']) && !is_null($an_item['token'])){//已关注
                $fromUsername = $an_item['token'];
                $toUsername = $an_item['openid'];
                $contentStr = "订单号:".$an_item['guid']."\n";
                $contentStr .= "下单时间: ".date('m-d H:i',$an_item['cTime'])."\n";

                $an_item['order_info'] = json_decode($an_item['order_info'],true);
                $info_text = "";
                foreach ($an_item['order_info']['item'] as $one) {
                    $info_text .= $one['name']." ￥".$one['price']."x".$one['count']."\n";
                }
                $info_text .= "备注:".$an_item['order_info']['note'];
                $contentStr .= "订单信息:\n".$info_text."\n----------------------------\n";
                $contentStr .= "此订单餐厅已确认";
                // $contentStr .= ORDER_PHONE;
                // echo "$contentStr";die;
                $res = send_msg($fromUsername, $toUsername, $contentStr);//向用户发送信息（文本）
                // if ($res ['errcode'] == 0) {
                    
                // }else {
                //     $this->error ( '发送取消订单消息失败，错误的返回码是：' . $res ['errcode'] . ', 错误的提示是：' . $res ['errmsg'] );
                // }
            }

            $this->success ( '订单确认成功！',U("Home/Orderitem/newOrders"),1);
        }    
    }


    // 设为无效
    function setInvalid(){
        if(IS_POST){
            // p(I('post.'));die;
            $guid = I('get.guid');

            $an_item = $this->model->where("guid = $guid")->find();
            // p($an_item);die;
            
            if(is_null($an_item['rTime'])){//未确认即取消的订单
                $update['rTime'] = NOW_TIME;
                $_0_order = true;

            }else{//已确认的订单（餐厅与用户协商后取消）

                $_0_order = false;
            }

            $update['status'] = 3;
            $update['reason'] = I('post.reason');
            if($this->model->where("guid = $guid")->setField($update)){//更新订单信息

                //判断是否有token和openid（即判断是否已经关注服务号）
                if(!is_null($an_item['openid']) && !is_null($an_item['token'])){//已关注
                    $fromUsername = $an_item['token'];
                    $toUsername = $an_item['openid'];
                    $contentStr = "订单号:".$an_item['guid']."\n";
                    $contentStr .= "下单时间: ".date('m-d H:i',$an_item['cTime'])."\n";

                    $an_item['order_info'] = json_decode($an_item['order_info'],true);
                    $info_text = "";
                    foreach ($an_item['order_info']['item'] as $one) {
                        $info_text .= $one['name']." ￥".$one['price']."x".$one['count']."\n";
                    }
                    $info_text .= "备注:".$an_item['order_info']['note'];
                    $contentStr .= "订单信息:\n".$info_text."\n----------------------------\n";
                    $contentStr .= "此订单无效！\n原因：".I('post.reason')."\n";
                    // $contentStr .= ORDER_PHONE;
                    // echo "$contentStr";die;
                    $res = send_msg($fromUsername, $toUsername, $contentStr);//向用户发送信息（文本）
                    // if ($res ['errcode'] == 0) {
                        
                    // }else {
                    //     $this->error ( '发送取消订单消息失败，错误的返回码是：' . $res ['errcode'] . ', 错误的提示是：' . $res ['errmsg'] );
                    // }
                }


                if($_0_order){
                    $this->success ( '订单取消成功！',U("Home/Orderitem/newOrders"));
                }else{
                    $this->success ( '订单取消成功！',U("Home/Orderitem/lists"));
                }
            }
        }else{

            $guid = I('get.guid');
            $this->assign('guid', $guid);
            $this->display();
        }
    }

    // 新订单查看
    function newOrders(){
        // 展示出未确认的订单
        // 订单的信息
        // 确认、无效、打印操作

        // JS轮询

        $rid = 10086 + session('uid');

        $the_day = date('Y-m-d');
        $map['rid'] = array('eq',$rid);
        $map['status'] = array('eq', 0);
        $orders = $this->model->where($map)->where("DATE_FORMAT(FROM_UNIXTIME(cTime),'%Y-%m-%d')='" . $the_day . "'")->select();

        foreach ($orders as $key => $value) {
            $orders[$key]['order_info'] = json_decode($orders[$key]['order_info'], true);
        }
        // p($orders);die;

        $this->assign('data', $orders);
        $this->display();
    }

    // 顾客订单记录
    function getUserInfo(){
        if(!I('get.phone')){//没有给出用户手机号
            E('手机号码错误！');
        }else{

            $today = date('Y-m-d');//今日
            $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳
            $last_month_days = getLastMonth_StartAndEnd($today);//上月第1日和最后1日，数组时间戳

            $map['phone'] = array('eq', I('get.phone'));
            //上个月1号--本月月尾   用户订单记录
            $temp = $this->model->where($map)->where("cTime between '".$last_month_days[0]."' and '".$month_days[1]."'")->select();
             
            //组合成的数组信息array(下单时间[已过x天]，金额，订单文本信息，地址，电话)
            foreach ($temp as $one_temp) {
                // p($one_temp);die;
                $one_data['phone'] = $one_temp['phone'];
                $one_data['address'] = $one_temp['address'];
                $one_data['total'] = $one_temp['total'];
                // $one_data['time'] = date('Y-m-d H:i', $one_temp['cTime']);
                // xx时间前的订单
                if($t = intval((NOW_TIME-$one_temp['cTime'])/86400)){
                    $one_data['pasttime'] = $t."天前";
                }else{
                    if($t = abs(intval((NOW_TIME-$one_temp['cTime'])/3600))){
                        $one_data['pasttime'] = $t."小时前";
                    }else{
                        $one_data['pasttime'] = abs(intval((NOW_TIME-$one_temp['cTime'])/60))."分钟前";
                    }                    
                }
                $order_info = json_decode($one_temp['order_info'],true);
                $info = '';
                $flag = true;
                foreach ($order_info['item'] as $an_order) {
                    // p($an_order);die;
                    if($flag){
                        $info .= $an_order['name']." ".$an_order['count']."份";
                        $flag = false;
                    }else{
                        $info .= "<br/>".$an_order['name']." ".$an_order['count']."份";
                    }
                }
                $one_data['info'] = $info;
                // echo $info;
                // p($one_data);die;
                $user_data[] = $one_data;
            }
            // p($user_data);die;

            $this->assign('data', $user_data);
            $this->display("userInfo");
        }
    }

}

?>