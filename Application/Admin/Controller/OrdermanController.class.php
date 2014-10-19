<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 订餐用户管理
 * 
 */
class OrdermanController extends AdminController{

    protected $model;
    public function _initialize() {
        parent::_initialize ();

        /* 是否有设置公众号 */
        has_token() || $this->error('您未设置默认公众号，请先设置！', U('Public/lists'));
        
        $this->model = M ('orderman');
    }

    public function lists(){

        /**
         * 分页
         */
        $map['token'] = session('token');
        $count = $this->model->where($map)->count();// 查询满足要求的总记录数
        //echo $count;
        $Page = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(20)
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $this->model->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('data',$list);

        $this->display();
    }

    public function edit(){
        $map['token'] = session('token');
        $map['id'] = I('get.id');
        if(IS_POST){
            $post = I('post.');
            if($this->model->where($map)->save($post) !== false){
                $this->success("修改成功",U('lists'));
            }else{
                $this->error("修改失败");
            }

        }else{
            // 获取数据
            $data = $this->model->where($map)->find();

            $this->assign ( 'data', $data );
            // $this->assign ( 'edit_id', $id );

            $this->display();
        }
    }

    public function del(){
        $map['token'] = session('token');
        $map['id'] = I('get.id');

        if($this->model->where($map)->delete()){
            $this->success("删除成功",U('Admin/Orderman/lists'));
        }else{
            $this->error("删除失败");
        }
    }

    public function userInfo(){
        if(!I('get.phone')){//没有给出用户手机号
            E('手机号码错误！');
        }else{

            $model = M('orderitem',' ');//订单模型
            $today = date('Y-m-d');//今日
            $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳
            $last_month_days = getLastMonth_StartAndEnd($today);//上月第1日和最后1日，数组时间戳

            $map['phone'] = array('eq', I('get.phone'));
            $model = M('orderitem', ' ');
            //上个月1号--本月月尾   用户订单记录
            $temp = $model->where($map)->select();
             
            //组合成的数组信息array(下单时间[]，金额，订单文本信息，地址，电话)
            foreach ($temp as $one_temp) {
                // p($one_temp);die;
                $one_data['phone'] = $one_temp['phone'];
                $one_data['address'] = $one_temp['address'];
                $one_data['total'] = $one_temp['total'];
                // $one_data['time'] = date('Y-m-d H:i', $one_temp['cTime']);
                // xx时间前的订单
                $one_data['cTime'] = date('Y-m-d', $one_temp['cTime']);
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

    // 启用/禁用用户
    // public function changeStatus(){
    //     $map['token'] = session('token');
    //     $map['id'] = I('get.id');

    //     $data = M('orderman')->where($map)->find();
    //     if($data['status']){
    //         $update['status'] = 0;
    //     }else{
    //         $update['status'] = 1;
    //     }
    // }

}

?>