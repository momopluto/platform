<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 订单管理
 *
 */
class OrderitemController extends AdminController{

    protected $model;

    public function _initialize() {
       parent::_initialize ();

        /* 是否有设置公众号 */
        has_token() || $this->error('您未设置默认公众号，请先设置！', U('Public/lists'));
        
        $this->model = D ('orderitem');
    }

    public function lists(){
//        p($_SERVER);die;
//        p( $this->model->select());die;
        // echo C("MODULE_PATH");die;
        // p(session());die;

        if(I('get.source') == 'news'){//查看新订单
            $map['rTime']  = array('exp',' is NULL');
        }else if(I('get.source') == 'urge'){//查看催单
            $map['status']  = 4;
        }elseif (I('get.source') == 'confirmed') {//已确认订单
            $map['status']  = 1;
        }elseif (I('get.source') == 'responsed') {//已响应催单
            $map['status']  = 5;
        }elseif (I('get.source') == 'finished') {//已完成订单
            $map['status']  = 2;
        }elseif (I('get.source') == 'canceled') {//已取消订单
            $map['status']  = 3;
        }
        $this->model = $this->model->where(array('token' => session('token')))->where($map);
        
        // 分页
        $count = $this->model->where(array('token' => session('token')))->count();// 查询满足要求的总记录数
        //echo $count;
        $Page = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(10)
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $this->model->where(array('token' => session('token')))->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('data',$list);
//        p($this->model->count());die;

        $this->display();
    }

    //管理员搜索订单
    public function search(){
        $keyword = I('get.keyword');
        if(IS_AJAX){
            $map['token'] = session('token');
            // $map['phone'] = array('like','%'.$keyword.'%');
            // $map['address'] = array('like','%'.$keyword.'%');
            // $result = M('orderitem')->where($map)->select();
            $result = M('orderitem')->query("select * from hxj_orderitem WHERE (phone like '%".$keyword."%') or (address like '%".$keyword."%') order by id desc");
            $count = count(M('orderitem')->query("select * from hxj_orderitem WHERE (phone like '%".$keyword."%') or (address like '%".$keyword."%')"));
//            echo $count;
            for ($i = 0 ; $i < $count; $i++) {

                $result[$i]['cTime'] = date('Y-m-d H:i', $result[$i]['cTime']);
                if($result[$i]['rTime'] != null){
                    $result[$i]['rTime'] = date('Y-m-d H:i', $result[$i]['rTime']);
                }
                switch($result[$i]['status']){
                    case 0;
                        $result[$i]['status'] = "新订单，未确认";
                        break;
                    case 1;
                        $result[$i]['status'] = "已确认";
                        break;
                    case 2;
                        $result[$i]['status'] = "订单完成";
                        break;
                    case 3;
                        $result[$i]['status'] = "订单取消";
                        break;
                    case 4;
                        $result[$i]['status'] = "用户催单";
                        break;
                    case 5;
                        $result[$i]['status'] = "已响应催单";
                        break;
                }
            }
            // $this->assign('list',$list);// 赋值数据集
            // $this->assign('page',$show);// 赋值分页输出
            // $this->display('lists');
            $this->ajaxreturn($result,'json');
        }
    }

    //管理员删除订单
    public function del(){
        $id = I('get.id');

        $data = $this->model->where(array('token' => session('token')))->where("id = $id")->find();
        if(empty($data)){
            $this->error('数据不存在！',U('Admin/Orderitem/lists'));
        }

        if($this->model->where(array('token' => session('token')))->where("id = $id")->delete()){
            $this->success("删除成功",U('Admin/Orderitem/lists'),3);
        }else{
            $this->error("删除失败");
        }
    }

    //订单完成，确认
    public function finish(){
        $id = I('get.id');
        $an_item = M('orderitem')->where("id = $id")->find();
        if($an_item['status'] == 2){//已完成订单
            $this->error("此订单已完成！");
        }

        if($an_item['status'] == 3){//已取消订单
            $this->error("此订单已取消！不能确认完成！");
        }

        $update['status'] = 2;
        M('orderitem')->where("id = $id")->setField($update);//更新订单信息

        $map ['token'] = $an_item['token'];
        $map ['openid'] = $an_item['openid'];
        $man_data = M('orderman')->where($map)->find();
        $update_man['success_count'] = $man_data['success_count'] + 1;//成功订单数+1
        M('orderman')->where($map)->setField($update_man);//更新订单用户信息

        $this->success ( '订单确认完成！' );
    }
    
    //管理员确认订单
    public function confirm(){
        $id = I('get.id');
        $an_item = M('orderitem')->where("id = $id")->find();
        if($an_item['rTime'] == null){//未响应的订单，向用户发送信息

            $captains_res = send_order_to_captains($an_item);//将订单信息发送给“送餐队长”

            if ($captains_res ['errorCode'] == 0) {
                if($an_item['rTime'] == null){
                    $update['rTime'] = NOW_TIME;
                }
                $update['status'] = 1;
                M('orderitem')->where("id = $id")->setField($update);//更新订单信息
                
                $this->success ( '订单确认成功！' . $captains_res ['errorCode'] . ', 错误的提示是：' . $captains_res ['errorMessages']  );
            }else{
                $this->error ( '订单确认失败，错误的返回码是：' . $captains_res ['errorCode'] . ', 错误的提示是：' . $captains_res ['errorMessages']  );
            }

//            $toUsername = $an_item['openid'];
//            $contentStr = "下单时间: ".date('m-d H:i',$an_item['cTime'])."\n";
//            $contentStr .= "订单信息:\n".$an_item['info_text']."----------------------------\n";
//            $contentStr .= "餐厅已确认，送餐中\n\n";
//            $contentStr .= ORDER_PHONE;
//            $res = send_msg($toUsername, $contentStr);//向用户发送信息（文本）

            /*服务号回复信息
            $toUsername = $an_item['openid'];
            $contentStr = "下单时间: ".date('m-d H:i',$an_item['cTime'])."\n";
            $contentStr .= "订单信息:\n".$an_item['info_text']."----------------------------\n";
            $contentStr .= "餐厅已确认，送餐中\n\n";
            $contentStr .= ORDER_PHONE;
            $res = send_msg($toUsername, $contentStr);//向用户发送信息（文本）

            if ($res ['errcode'] == 0) {

                if($an_item['rTime'] == null){
                    $update['rTime'] = NOW_TIME;
                }
                $update['status'] = 1;
                M('orderitem')->where("id = $id")->setField($update);//更新订单信息

                $this->success ( '订单确认成功！' );
            } else {
                $this->error ( '订单确认失败，错误的返回码是：' . $res ['errcode'] . ', 错误的提示是：' . $res ['errmsg'] );
            }
            */
        }elseif($an_item['status'] == 4){
            //TODO，提醒送餐员
            if($an_item['rTime'] == null){
                $update['rTime'] = NOW_TIME;
            }
            $update['status'] = 5;
            if(M('orderitem')->where("id = $id")->setField($update)){//更新订单信息                
                $this->success ( '催单响应成功！' );
            }else{
                $this->error ('响应失败！');
            }
        }else{
            $this->error ("此订单已确认！");
        }        
    }

    //管理员取消订单（无效）
    public function cancel(){
        $id = I('get.id');
        $an_item = M('orderitem')->where("id = $id")->find();
        if(IS_POST){            
//            p($_POST);die;
            if($an_item['rTime'] == null){
                $update['rTime'] = NOW_TIME;
            }
            $update['status'] = 3;
            $update['note'] = I('post.reason');
            M('orderitem')->where("id = $id")->setField($update);//更新订单信息
            $this->success ( '订单取消成功！',U("Admin/Orderitem/lists"));

            /*
            $toUsername = $an_item['openid'];
            $contentStr = "！！！\n下单时间: ".date('m-d H:i',$an_item['cTime'])."\n";
            $contentStr .= "订单信息:\n".$an_item['info_text']."----------------------------\n";
            $contentStr .= "此订单无效！\n原因：".I('post.reason')."\n";
            $contentStr .= ORDER_PHONE;
            $res = send_msg($toUsername, $contentStr);//向用户发送信息（文本）
            if ($res ['errcode'] == 0) {
                if($an_item['rTime'] == null){
                    $update['rTime'] = NOW_TIME;
                }
                $update['status'] = 3;
                M('orderitem')->where("id = $id")->setField($update);//更新订单信息
                $this->success ( '订单取消成功！',U("Admin/Orderitem/lists"));
            } else {
                $this->error ( '订单取消失败，错误的返回码是：' . $res ['errcode'] . ', 错误的提示是：' . $res ['errmsg'] );
            }
            */
        }else{
            if($an_item['status'] == 2){//已完成订单
                $this->error("此订单已完成！不能设置无效！");
            }
            $this->assign('id', $id);
            $this->display();
        }
    }

    //JS轮询调用的订单监视方法
    public function monitor_order(){
        if(IS_AJAX){
            $param ['token'] = session('token');

            $param['rTime'] = array('exp',' is NULL');//新订单标志
            $model = M('orderitem');

            $_0count = $model->where($param)->count();

            unset($param['rTime']);
            $param['status'] = 4;//催单标志
            $_4count = $model->where($param)->count();

            if($_0count || $_4count){
                $data['_0count'] = $_0count;
                $data['_4count'] = $_4count;
                $data['flag'] = 1;
            }else{
                $data['flag'] =0;
            }
            $this->ajaxreturn($data, 'json');
        }
    }
}
?>