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


    // 下单成功与否
    function done(){

    }


    // 送餐信息
    function info(){
        
        session('pltf_openid', 'o55gotzkfpEcJoQGXBtIKoSrairQ');

        if(IS_POST){

            if (session('?pltf_curRst_info')) {

                // 再次验证餐厅状态
                $rst = rstInfo_combine(session('pltf_curRst_info'));

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
                $this->error('Something Wrong！', U('Client/Order/lists'));
            }
            
        }else{

            redirect(U('Client/Order/lists'));
        }

    }


    // 购物车
    function cart(){
/*
        if (!is_null(cookie('pltf_order_cookie'))) {
            if(IS_POST){
                $this->display();
            }else{
                $this->error('请在餐厅内下单哦！', U('Client/Order/lists'));
            }
        }else{//没有cookie
            if(IS_POST){
                $this->error('美食篮是空的～您还没选餐哦！', U('Client/Order/lists'));
            }else{
                $this->error('Something Wrong！', U('Client/Order/lists'));
            }
        }
*/

        if(IS_POST){
            
            if(!is_null(cookie('pltf_order_cookie'))){
                // echo "111";die;
                $this->display();
            }else{
                // echo "222";die;
                $this->success('美食篮空空如也，快去挑选餐厅选餐吧！', U('Client/Order/lists'), 3);
            }
        }else{
            redirect(U('Client/Order/lists'));
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
                    $this->error('Something Wrong！', U('Client/Order/lists'));
                }
            }else{
                 $this->error('Something Wrong！', U('Client/Order/lists'));
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
                redirect(U('Client/Order/lists'));
            }
        }
    }

    // 所有餐厅展示，供选择
    function lists(){
        
        // if($isWechat){//微信，此状态也写入session
            
        //     if(I('get.openid') != null){// 公众号
        //         // openid写入session
        //     }

        //     if("得到Author.openid"){// 服务号
        //         // 得到openid，并写入session
        //         // 是否粉丝
        //     }


        // }else{//PC浏览器等
        //     if(session('?pltf_phone')){

        //         // 存在用户手机号，但不确定是否是本人，在info.html显示出来后，用户可决定是否修改送餐信息
        //         // info.html中要特别处理已存在送餐信息的情况

        //     }else{
        //         // 什么都不做
        //         // 之后步骤同样通过判断session('?pltf_phone')即可
        //     }
        // }


        // 1.在admin_allrst中过滤出开启了平台服务的餐厅
        //      rid、rst_name、token
        // 2.通过以上rid信息，在home_resturant中取得餐厅的详细信息
        //      rid,logo_url,rst_name,isOpen[餐厅状态，主观人为设置是否营业，最高优先级]、
        //      rst_is_bookable,rst_agent_fee,stime_*_open,stime_*_close
        // 3.根据stime_*_open、stime_*_close判断当前是否为"自动"营业时间
        // 4.在[orderitem]/menu中统计上月售、本月售[餐厅排序依据]
        // 5.每家餐厅都整合以上信息，构成两个大的多维数组（open  &  close），以上月售、本月售降序排序
        
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

            $an_rst = rstInfo_combine($an_rst);// 订餐页面所需要的餐厅的信息，组装

            if($an_rst['isOpen'] == "1"){//主观，营业
                // echo $an_rst['open_status']."status！";die;
                if(intval($an_rst['open_status']) % 10 == 4){//已过餐厅今天的所有营业时间
                    // echo $an_rst['rid']."打烊了啊！";die;
                    $close_rsts[$key] = $an_rst;
                }else{
                    if($an_rst['rst_is_bookable']){
                        $open_rsts[$key] = $an_rst;
                    }else{
                        if($an_rst['open_status'] == "1" || $an_rst['open_status'] == "2" || $an_rst['open_status'] == "3"){
                            $open_rsts[$key] = $an_rst;
                        }else{
                            $close_rsts[$key] = $an_rst;
                        }
                    }
                } 
            }else{//主观，其它，非营业
                $close_rsts[$key] = $an_rst;
            }    
        }

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

        // p($open_rsts);
        // echo "<hr/>";
        // p($close_rsts);die;
         
        $this->assign('open_rsts', $open_rsts);
        $this->assign('close_rsts', $close_rsts);

        $this->display();

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