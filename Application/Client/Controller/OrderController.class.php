<?php
namespace Client\Controller;
use Think\Controller;
class OrderController extends ClientController {
    public function index(){
        echo "Clinet/Order/index.html";
    }


    // 查询订单
    function myOrder(){

    //假定给出session('phone')或者session('openid')
    	
        // session('pltf_phone', '18826666666');
        // session('pltf_phone', '18826481053');
        // session('pltf_openid', 'o55gotzkfpEcJoQGXBtIKoSrairQ');
        // session(null);

        $o_model = M('orderitem',' ');
        $r_model = M('resturant','home_');

        $has_order = true;
        if(session('?pltf_phone')){
        // 有phone
            $map['phone'] = session('pltf_phone');
        }else{

            if(session('?pltf_openid')){
            // 有openid
                $whe['openid'] = session('pltf_openid');
                // 在orderman中通过openid对应phone
                $map = M('orderman','admin_')->where($whe)->field('phone')->find();
                if(!is_null($map)){
                //该openid已有对应的phone（最后一次在该openid上使用的手机）
                
                    // session('pltf_openid',null);//删除openid
                    session('pltf_phone', $map['phone']);//将phone写入session
                }else{
                // 有openid，但是没有对应的phone
                    $has_order = false;
                }
            }else{
            // 没有phone 也没有openid
                $has_order = false;
            }

        }
    	
        // 得到了phone后，开始查数据
        if($has_order){

            $t_brief = $o_model->where($map)->order('cTime desc')->field('guid,cTime,rid,total,status')->select();
            
            if(!is_null($t_brief)){
            // 存在订单数据
                $rsts = $r_model->getField('rid,logo_url,rst_name');

                foreach ($t_brief as $one) {
                    $one['logo_url'] = $rsts[$one['rid']]['logo_url'];
                    $one['rst_name'] = $rsts[$one['rid']]['rst_name'];
                    $one['cTime'] = date('n.d H:i', $one['cTime']);

                    unset($one['rid']);

                    $data[] = $one;
                    // p($one);die;
                }
                // p($data);die;
                $this->assign('data',$data);
            }
        }

        $this->display('order');
    }

    // 订单详情
    function detail(){
        
        if(!I('get.id')){
            E('无效订单号');
        }else{
            $map['guid'] = I('get.id');
        }
        $map['phone'] = session('pltf_phone');

        $o_model = M('orderitem',' ');
        $r_model = M('resturant','home_');

        $order = $o_model->where($map)->find();
        if(!is_null($order)){
            // p($order);die;

            $whe['rid'] = $order['rid'];
            $rstinfo = $r_model->where($whe)->field('logo_url,rst_name,rst_phone')->find();

            $brief['rid'] = 10086 * $order['rid'];//此处rid加密
            $brief['logo_url'] = $rstinfo['logo_url'];
            $brief['rst_name'] = $rstinfo['rst_name'];
            $brief['cTime'] = date('Y-n-d H:i', $order['cTime']);
            $brief['total'] = $order['total'];
            $brief['status'] = $order['status'];
            // p($rstinfo);die;
            
            $data['rst_phone'] = $rstinfo['rst_phone'];
            $data['brief'] = $brief;

            $o_info = json_decode($order['order_info'],true);
            $i_count = 0;
            foreach ($o_info['item'] as $an_item) {
                $i_count += $an_item['count'];
            }
            $data['count'] = $i_count;
            $data['total'] = $order['total'];
            $data['items'] = $o_info['item'];
            $data['name'] = $order['name'];
            $data['phone'] = $order['phone'];
            $data['address'] = $order['address'];
            $data['guid'] = $order['guid'];
            $data['note'] = $o_info['note'];
            // p($data);die;
            
            $this->assign('data', $data);
        }
        
    	$this->display();
    }



    /*array格式订单信息
        (
            [total] => 50
            [item] => Array
                (
                    [0] => Array
                        (
                            [name] => \u5c0f\u83dc00
                            [price] => 10
                            [count] => 2
                            [total] => 20
                        )

                    [1] => Array
                        (
                            [name] => \u5c0f\u83dc11
                            [price] => 10
                            [count] => 3
                            [total] => 30
                        )

                )

            [note] => testtttttttttttttttttt
        )
     */
    /*json格式订单信息
        {
            "total": "50",
            "item": [{
                "name": "\u5c0f\u83dc00",
                "price": "10",
                "count": "2",
                "total": "20"
            }, {
                "name": "\u5c0f\u83dc11",
                "price": "10",
                "count": "3",
                "total": "30"
            }],
            "note": "testtttttttttttttttttt"
        }

        {
             "total": 14,
             "items": [{
                 "entity_id": 5472819,
                 "name": "\u5c0f\u9ec4\u95f7\u9e21\u7c73\u996d\uff08\u5b66\u751f\u4ef7\uff09",
                 "price": 12,
                 "parent_entity_id": 0,
                 "group_id": 1,
                 "quantity": 2,
                 "entity_category_id": 1
             }, {
                 "entity_id": 4858126,
                 "name": "\u67e0\u6aac\u8336",
                 "price": 3,
                 "parent_entity_id": 0,
                 "group_id": 1,
                 "quantity": 1,
                 "entity_category_id": 1
             }, {
                 "entity_id": 1331,
                 "name": "\u7acb\u51cf\u4f18\u60e0",
                 "price": -13,
                 "parent_entity_id": 0,
                 "group_id": 0,
                 "quantity": 1,
                 "entity_category_id": 12
             }],
             "isWaimai": true,
             "isTangchi": false,
             "isBook": true,
             "deliverTime": "12\u70b930\u5206",
             "restaurantNumber": 20,
             "createdAt": "2014-09-29 10:34:02",
             "table": "",
             "people": "",
             "phone": "18826485288",
             "address": "\u534e\u5c7114\u680b503",
             "description": "",
             "isFollower": false,
             "isOnlinePayment": true,
             "invoice": "",
             "isTimeEnsure": false,
             "timeEnsureText": ""
         };
    */



    // 送餐信息
    function info(){
        p(I('post.'));die;
        if(IS_POST){

        }
        
        $this->display();
    }


    // 购物车
    function cart(){

        if(IS_POST){
            $json_text = I('post.postData','','');
            $data = json_decode($json_text,true);
            // p($data);
            
            // //设置cookie，系列化数组(因为cookie不支持数组)
            cookie('pltf_order_cookie', $json_text, 3600);
            // p(cookie());die;
            // echo "<hr>";
            // p(unserialize(cookie('pltf_order_cookie')));die;

            //购物车必用post过来的数据data！！！
            $this->assign('data', $data);
        }

        // p(cookie());
        // p(I('post.','',''));die;
        
        $this->display();
    }


    // 餐厅菜单
    function menu(){
        
        // cookie('pltf_order_cookie',null);
        // p(cookie());die;
        

        $rid = 10456;//伪造数据，测试


        if (IS_POST) {
            $data = I('post.postData','','');
            p($data);die;
            // $data = $this->object_to_array(I('post.postData'));
            p($data);
        }else{
            $data = M('menu',$rid.'_')->select();
            $rstinfo = M('resturant','home_')->where("rid = $rid")->find();
            // p($rstinfo);die;
            $this->assign('data', $data);//菜单列表
            $this->assign('rst', $rstinfo);//餐厅信息
            $this->display();
        }
    }

    // 所有餐厅
    function lists(){

        // 1.在admin_allrst中过滤出开启了平台服务的餐厅
        //      rid、rst_name、token
        // 2.通过以上rid信息，在home_resturant中取得餐厅的详细信息
        //      rid,logo_url,rst_name,isOpen[餐厅状态，主观人为设置是否营业，最高优先级]、
        //      rst_is_bookable,rst_agent_fee,stime_*_open,stime_*_close
        // 3.根据stime_*_open、stime_*_close判断当前是否为"自动"营业时间
        // 4.在[orderitem]/menu中统计上月售、本月售[餐厅排序依据]
        // 5.每家餐厅都整合以上信息，构成一个大的多维数组，以上月售、本月售降序排序
        
        $token = "gh_34b3b2e6ed7f";//默认

        $on_rsts = M('allrst','admin_')->where("status=1")->select();
        
        $str = "";
        foreach ($on_rsts as $key => $one_rst) {
            if($key != 0){
                $str .= ",".$one_rst['rid'];
            }else{
                $str .= $one_rst['rid'];
            }
        }
        // $In['rid'] = array('in','10456,10464');
        $In['rid'] = array('in', $str);//过滤条件，rid必须in在$str所给出的范围里

        // 得到以rid为键名的多维数组
        $rsts = M('resturant','home_')->where($In)->getField('rid,logo_url,rst_name,isOpen,rst_is_bookable,rst_agent_fee,
            stime_1_open,stime_1_close,stime_2_open,stime_2_close,stime_3_open,stime_3_close');

        $open_rsts = array();
        $close_rsts = array();
        foreach ($rsts as $key => $an_rst) {
            if($an_rst['isOpen'] == 1){//主观，营业
                echo "9";
                // 判断是否营业时间
                $n_time = date('H:i');

                $is_closed = false;
                // var_dump($an_rst['stime_3_open'] !== '');die;
                if($an_rst['stime_3_open'] !== '' && $an_rst['stime_3_close'] !== ''){
                    echo "8";
                    if (strtotime($an_rst['stime_3_open']) <= strtotime($n_time) && strtotime($n_time) <= strtotime($an_rst['stime_3_close'])){
                        echo "7";
                        // 目前处于第3营业时间
                        $open_status = 3;
                    }elseif (strtotime($n_time) > strtotime($an_rst['stime_3_close'])) {
                        echo "6";
                        $is_closed = true;
                    }
                }elseif($an_rst['stime_2_open'] !== '' && $an_rst['stime_2_close'] !== ''){
                    echo "5";
                    if (strtotime($an_rst['stime_2_open']) <= strtotime($n_time) && strtotime($n_time) <= strtotime($an_rst['stime_2_close'])){
                        echo "4";
                        // 目前处于第2营业时间
                        $open_status = 2;
                    }elseif (strtotime($n_time) > strtotime($an_rst['stime_2_close'])) {
                        echo "3";
                        $is_closed = true;
                    }
                }elseif(strtotime($an_rst['stime_1_open']) <= strtotime($n_time) && strtotime($n_time) <= strtotime($an_rst['stime_1_close'])){
                    echo "2";
                    // 目前处于第1营业时间
                    $open_status = 1;
                }elseif(strtotime($n_time) > strtotime($an_rst['stime_1_close'])){
                    echo "1";
                    $is_closed = true;
                }else{
                    echo "0";
                    $open_status = 0;
                }

                $an_rst['open_status'] = $open_status;

                $an_rst['month_sale'] = M('menu', $key."_")->sum('month_sale');//本月销售量
                $an_rst['last_month_sale'] = M('menu', $key."_")->sum('last_month_sale');//本月销售量
                // echo "<br/>$key - $month_sale";die;

                if($an_rst['rst_is_bookable'] || $an_rst['open_status'] != 0){
                    echo "-1";
                    if(!$is_closed){
                        echo "-2";
                        $open_rsts[$key] = $an_rst;
                    }else{
                        echo "-3";
                        $close_rsts[$key] = $an_rst;
                    }
                }else{
                    echo "-4";
                    $close_rsts[$key] = $an_rst;
                }
            }else{//主观，其它，非营业
                echo "-5";
                $close_rsts[$key] = $an_rst;
            }

        }

        // p($open_rsts);
        // echo "<hr/>";
        // p($close_rsts);

        // 营业/非营业，各自排序
        $today = date('Y-m-d');//今日
        $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳

        if (strtotime($today) != $month_days[0]) {
            //不是每月第1天，以本月售为排序标准
            uasort($open_rsts, 'compare_month_sale');//降序
            uasort($close_rsts, 'compare_month_sale');//降序
        }else{
            //本月第1天，以上月销售为排序标准
            uasort($open_rsts, 'compare_last_month_sale');//降序
            uasort($close_rsts, 'compare_last_month_sale');//降序
        }

        p($open_rsts);
        echo "<hr/>";
        p($close_rsts);die;

        $this->display();

    }

/*
    // 用户下单(生成guid和today_sort)
    function submit_order(){
        if(IS_POST){
            // p(I('post.'));die;
            //下单，进入orderitem订单数据库前，先从today数据库中得到唯一的自增$today_sort，组合成$guid
            
    //此处rid为测试用，实际使用时rid通过post获得
            $rid = 10456;
            $model = M('today', $rid.'_');
            $data['rid'] = $rid;
            if($model->create($data) && $today_sort = $model->add($data)){
                //$guid订单号，$today_sort今天第xx份订单
                $guid = strval(1800 + $today_sort).strval($rid).strval(NOW_TIME);//19位
                echo "guid = ".$guid."<br/>today_sort = " . $today_sort."<br/>";
//TODO，传过来订单信息JSON的订单内容，及用户的联系方式、地址等
    //插入数据表orderitem
    
                // p(I('post.'));die;
                $order_info = I('post.arr');//TODO，此为前端计算生成的订单信息数组
                $temp['rid'] = $rid;
                $temp['guid'] = $guid;
                $temp['today_sort'] = $today_sort;
                $temp['token'] = "gh_34b3b2e6ed7f";
                $temp['openid'] = "o55gotzkfpEcJoQGXBtIKoSrairQ";
                $temp['name'] = I('post.name');
                $temp['phone'] = I('post.phone');
                $temp['address'] = I('post.address');

                $temp['total'] = $order_info['total'];
                $temp['order_info'] = json_encode($order_info);
                $temp['cTime'] = NOW_TIME;

                // p($temp);die;

                $_model = M('orderitem', ' ');
                if($id = $_model->add($temp)){
                    echo '$id = '.$id;
                }else{
                    $this->error($_model->getError());
                }
            }else{
                $this->error($model->getError());
            }
        }else{
            $this->display();
        }   
    }

*/


// 测试*******************************************************========================================

    function test(){
        if(IS_POST){
            // p(I('post.'));die;
            //下单，进入orderitem订单数据库前，先从today数据库中得到唯一的自增$today_sort，组合成$guid
            
    //此处rid为测试用，实际使用时rid通过post获得
            $rid = 10456;
            $model = M('today', $rid.'_');
            $data['rid'] = $rid;
            if($model->create($data) && $today_sort = $model->add($data)){
                //$guid订单号，$today_sort今天第xx份订单
                $guid = strval(1800 + $today_sort).strval($rid).strval(NOW_TIME);//19位
                echo "guid = ".$guid."<br/>today_sort = " . $today_sort."<br/>";
//TODO，传过来订单信息JSON的订单内容，及用户的联系方式、地址等
    //插入数据表orderitem
    
                // p(I('post.'));die;
                $order_info = I('post.arr');//TODO，此为前端计算生成的订单信息数组
                $temp['rid'] = $rid;
                $temp['guid'] = $guid;
                $temp['today_sort'] = $today_sort;
        //此处的token和openid该从session中取
                $temp['token'] = "gh_34b3b2e6ed7f";
                $temp['openid'] = "o55gotzkfpEcJoQGXBtIKoSrairQ";
                $temp['name'] = I('post.name');
                $temp['phone'] = I('post.phone');
                $temp['address'] = I('post.address');

                $temp['total'] = $order_info['total'];
                $temp['order_info'] = json_encode($order_info);
                $temp['cTime'] = NOW_TIME;

                // p($temp);die;

                $_model = M('orderitem', ' ');
                if($id = $_model->add($temp)){
                    echo '$id = '.$id;
                }else{
                    $this->error($_model->getError());
                }
            }else{
                $this->error($model->getError());
            }
        }else{
            $this->display();
        }
    }


}