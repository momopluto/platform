<?php
namespace Client\Controller;
use Think\Controller;
class RestaurantController extends ClientController {


    // 所有餐厅展示，供选择
    function lists(){
        
        if($isWechat){//微信，此状态也写入session
            
            if(I('get.openid') != null){// 公众号
                // openid写入session
            }

            if("得到Author.openid"){// 服务号
                // 得到openid，并写入session
                // 是否粉丝
            }


        }else{//PC浏览器等
            if(session('?pltf_phone')){

                // 存在用户手机号，但不确定是否是本人，在info.html显示出来后，用户可决定是否修改送餐信息
                // info.html中要特别处理已存在送餐信息的情况

            }else{
                // 什么都不做
                // 之后步骤同样通过判断session('?pltf_phone')即可
            }
        }


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

    function info(){

        if(session('?pltf_curRst_info')){

            $curRst = session('pltf_curRst_info');

            $rid = $curRst['rid'];
            // echo "$rid";
            $data = M('resturant', 'home_')->where("rid = $rid")->find();
            if($data){
                $this->assign('data',$data);
            }
        }

        // p($data);die;

        $this->display();
    }

}

?>