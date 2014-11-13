<?php
namespace Client\Controller;
use Think\Controller;
class OrderController extends ClientController {


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


    // 下单成功与否
    function done(){
        // echo NOW_TIME;die;
        if(IS_POST){

            // p(cookie('pltf_curRst_info'));
            // p(cookie('pltf_order_cookie'));die;

            $json_order = cookie('pltf_order_cookie');
            if($json_order){

                $order = json_decode($json_order, true);
                // p($order);die;



                $rid = $order['rid'];
                $model = M('today', $rid.'_');

                $data['rid'] = $rid;
                if($model->create($data) && $today_sort = $model->add($data)){
                    //$guid订单号，$today_sort今天第xx份订单
                    $guid = strval(1800 + $today_sort).strval($rid).strval(NOW_TIME);//19位
                    echo "guid = ".$guid."<br/>today_sort = " . $today_sort."<br/>";

                    $temp['rid'] = $rid;
                    $temp['guid'] = $guid;
                    $temp['today_sort'] = $today_sort;

//****************************************此处的token和openid该从session中取
                    $temp['token'] = "gh_34b3b2e6ed7f";
                    $temp['openid'] = "o55gotzkfpEcJoQGXBtIKoSrairQ";

                    $temp['name'] = $order['c_name'];
                    $temp['address'] = $order['c_address'];
                    $temp['phone'] = $order['c_phone'];
                    $temp['total'] = $order['total'];
                    $temp['order_info'] = $json_order;
                    $temp['cTime'] = $order['cTime'];

                    // p($temp);die;

                    $_model = M('orderitem', ' ');
                    if($id = $_model->add($temp)){
                        echo '$id = '.$id;die;
                        $this->display();
                    }else{
                        $this->error($_model->getError());
                    }
                }

            }else{
                $this->error('Something Wrong！', 'Client/Restaurant/lists');
            }
            

        }else{

            // redirect(U('Client/Restaurant/lists'));
            $this->redirect('Client/Restaurant/lists');
        }

    }


    // 送餐信息
    function delivery(){
        
        session('pltf_openid', 'o55gotzkfpEcJoQGXBtIKoSrairQ');

        if(IS_POST){

            if (session('?pltf_curRst_info')) {

                // 获取餐厅rid，再次验证餐厅状态
                $rst = session('pltf_curRst_info');
                $rid = $rst['rid'];

                // 重新访问数据库获取信息
                $rst = M('resturant','home_')->where("rid = $rid")->field('rid,logo_url,rst_name,isOpen,rst_is_bookable,rst_agent_fee,
                    stime_1_open,stime_1_close,stime_2_open,stime_2_close,stime_3_open,stime_3_close')->find();
                
        //********************************重要验证，通过才能完成下单**********************************
                if (!$rst || !$rst['isOpen']) {//空则跳转,isOpen＝0餐厅休息，无法完成下单操作
                    $this->error('Something Wrong！', 'Client/Restaurant/lists');
                }

                $rst = rstInfo_combine($rst);
                session('pltf_curRst_info', $rst);//更新当前餐厅信息，写入session

                $rst['logo_url'] = urlencode($rst['logo_url']);//处理logo_url链接
                $json_rst = json_encode($rst);
                // p($rst);die;
                // p($json_rst);die;
                cookie("pltf_curRst_info", urldecode($json_rst));//更新当前餐厅信息，写入cookie

                // cookie(null,'pltf_'); // 清空指定前缀的所有cookie值

                $s_times = cut_send_times($rst);

                $this->assign('s_times', $s_times);

                if(session('?pltf_openid')){
                    $map['openid'] = session('pltf_openid');
                    $c_info = M('orderman', 'admin_')->where($map)->field('name, phone, address')->find();
                    if(!is_null($c_info)){

                        $this->assign('c_info', $c_info);

                        // p($c_info);die;
                    }
                }
                
                $this->display();
            }else{
                $this->error('Something Wrong！', 'Client/Restaurant/lists');
            }
            
        }else{

            // redirect(U('Client/Restaurant/lists'));
            $this->redirect('Client/Restaurant/lists');
        }

    }


    // 购物车
    function cart(){
/*
        if (!is_null(cookie('pltf_order_cookie'))) {
            if(IS_POST){
                $this->display();
            }else{
                $this->error('请在餐厅内下单哦！', 'Client/Restaurant/lists');
            }
        }else{//没有cookie
            if(IS_POST){
                $this->error('美食篮是空的～您还没选餐哦！', 'Client/Restaurant/lists');
            }else{
                $this->error('Something Wrong！', 'Client/Restaurant/lists');
            }
        }
*/

        if(IS_POST){
            
            if(!is_null(cookie('pltf_order_cookie'))){
                // echo "111";die;
                $this->display();
            }else{
                // echo "222";die;
                $this->success('美食篮空空如也，快去挑选餐厅选餐吧！', 'Client/Restaurant/lists', 3);
            }
        }else{
            // redirect(U('Client/Restaurant/lists'));
            $this->redirect('Client/Restaurant/lists');
        }

    }


    // 对应餐厅的菜单
    function menu(){
        
        // cookie('pltf_order_cookie',null);
        // cookie('pltf_curRst_info',null);
        // p(cookie(''));die;

        // $rid = 10456;//伪造数据，测试

        if (IS_POST) {
            // p(I('post.'));die;
            if(I('post.rid') != ""){
                $rid = I('post.rid') / 10086;//简单加密的解密

                // 得到rid餐厅信息
                $rst = M('resturant','home_')->where("rid = $rid")->field('rid,logo_url,rst_name,isOpen,rst_is_bookable,rst_agent_fee,
                    stime_1_open,stime_1_close,stime_2_open,stime_2_close,stime_3_open,stime_3_close')->find();

                if(!is_null($rst)){
                    $rst = rstInfo_combine($rst);// 订餐页面所需要的餐厅的信息，组装

                    session('pltf_curRst_info', $rst);//将当前选择的餐厅信息写入session

                    $rst['logo_url'] = urlencode($rst['logo_url']);//处理logo_url链接
                    $json_rst = json_encode($rst);
                    // p($rst);
                    // p($json_rst);die;
                    cookie("pltf_curRst_info", urldecode($json_rst));//将当前选择的餐厅信息写入cookie

                    // p(session('pltf_curRst_info'));die;

                    $data = M('menu',$rid.'_')->select();
                    $this->assign('data', $data);//菜单列表
                    $this->assign('rst', $rst);//餐厅信息

                    $this->display();
                }else{
                    $this->error('Something Wrong！', 'Client/Restaurant/lists');
                }
            }else{
                 $this->error('Something Wrong！', 'Client/Restaurant/lists');
            }
        }else{
            if(session('?pltf_curRst_info')){
                // p(session('pltf_curRst_info'));die;
                // $rst = json_decode(cookie('pltf_curRst_info'),true);
                $rst = session('pltf_curRst_info');

                $data = M('menu',$rst['rid'].'_')->select();
                $this->assign('data', $data);//菜单列表
                $this->assign('rst', $rst);//餐厅信息

                $this->display();
            }else{
                // redirect(U('Client/Restaurant/lists'));
                $this->redirect('Client/Restaurant/lists');
            }
        }
    }


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