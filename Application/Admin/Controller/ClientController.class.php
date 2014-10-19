<?php
namespace Admin\Controller;
use Think\Controller;

class ClientController extends Controller{

	//绑定信息
    public function bind_info(){
        if(IS_POST){
            //p($_POST);die;
            $_POST['receiver_phone'] = htmlspecialchars(trim($_POST['receiver_phone']));
            $_POST['takeout_address'] = htmlspecialchars(trim($_POST['takeout_address']));

            $model = D('orderman');
            if($model->where("id = $_POST[id]")->create($_POST) && $model->where("id = $_POST[id]")->setField($_POST)){
                $this->success("设置成功",U("Admin/Client/bind_info/source/$_GET[source]"));
            }else{
                $this->error($model->getError());
            }

        }else{
            // 获取数据
            if(I('get.openid') != null && I('get.token') != null){//去掉url带上的openid和token
                session('openid', I('get.openid'));
                session('token', I('get.token'));
                redirect(U("Admin/Client/bind_info/source/$_GET[source]"));
                return;
            }
            $param ['openid'] = get_openid();
            $param ['token'] = get_token();

            //此处限制不能转发绑定信息
            if($param ['token'] == -1 || $param ['openid'] == -1){
                E("转发此链接无效！须自行关注“黄小吉”后，点击“绑定信息”操作！");
            }

            $model = M('orderman');

            if($model->where($param)->count() == 0){
                $model->add($param);
            }
            $data = $model->where($param)->field(array('id, receiver_phone, takeout_address'))->find();

            if(I('get.source') != null){
                $data['source'] = I('get.source');
            }

            $this->assign ( 'data', $data );

            $this->display();
        }
    }

    //用户提交订单
    public function submit_order(){
        //判断是否订餐中标志status
        //      不是，则提示未选餐
        //      是，将信息写入订单数据库，重置ordermam表中相关数据，status=0，order_count+1，如果是第一次订餐，更新orderman表中cTime
        //          返回”提交订单“+”修改送餐信息“+”订单信息“

        // 获取数据
        if(I('get.openid') != null && I('get.token') != null){//去掉url带上的openid和token
            session('openid', I('get.openid'));
            session('token', I('get.token'));
            redirect(U("Admin/Client/submit_order"));
            return;
        }
        $param ['openid'] = get_openid();
        $param ['token'] = get_token();

        //此处限制不能转发确认下单信息
        if($param ['token'] == -1 || $param ['openid'] == -1){
            E("转发此链接无效！");
        }

        $man_model = M('orderman');
        $item_model = M('orderitem');

        $one = $man_model->where($param)->find();
        if($one['receiver_phone'] == null || $one['takeout_address'] == null){
            E("您未绑定送餐信息，无法下单！");
        }
        if($one['status'] == 0){
            E("该订单已提交！重复提交无效！");
        }

        $an_item['token'] = $one['token'];
        $an_item['openid'] = $one['openid'];
        $an_item['info_1'] = $one['info_1'];
        $an_item['info_2'] = $one['info_2'];
        $an_item['info_3'] = $one['info_3'];
        $an_item['info_4'] = $one['info_4'];
        $an_item['info_count'] = $one['info_count'];
        $an_item['total'] = $one['total'];
        $an_item['info_text'] = $one['info_text'];
        $an_item['phone'] = $one['receiver_phone'];
        $an_item['address'] = $one['takeout_address'];
        $an_item['cTime'] = NOW_TIME;
        $an_item['rTime'] = NOW_TIME+2;
        $id = $item_model->where($param)->add($an_item);//将订单信息写入订单表orderitem

//        nl2br($an_item['info_text']);
        $this->assign('data', $an_item);


        if($one['cTime'] == null){
            $one['cTime'] = $an_item['cTime'];
        }
        $one['status'] = 0;
        $one['order_count'] = $one['order_count'] + 1;
        $one['info_1'] = 0;
        $one['info_2'] = 0;
        $one['info_3'] = 0;
        $one['info_4'] = 0;
        $one['info_count'] = 0;
        $one['total'] = 0;
        $one['info_text'] = null;
        $man_model->where($param)->save($one);//更新订单用户信息表orderman

        //直接将新订单发送给“送餐队长”
        $an_item = M('orderitem')->where("id = $id")->find();
        $captains_res = send_order_to_captains($an_item);//将订单信息发送给“送餐队长”

        $this->display();
    }
}

?>