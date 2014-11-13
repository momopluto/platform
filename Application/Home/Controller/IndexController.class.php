<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 前台主页
 * 
 */
class IndexController extends HomeController {

    public function _initialize() {
        parent::_initialize ();

        /* 限制一定要设置了餐厅信息才能进行其它操作 */
        $rid = 10086 + session('uid');
        has_rstInfo($rid) || $this->error('您未初始化设置餐厅信息，请先设置！', U('Shopmanage/set'));
    }
    

    // 主页
    public function index(){
        // p(session());die;
        $rid = 10086 + session('uid');
        $model = M('resturant');

        $status = $model->where("rid = $rid")->field('isOpen')->find();
        session('isOpen', $status['isOpen']);

        // $this->assign('isOpen', $status['isOpen']);
        $this->display();
    }

    // 退出
    public function quit(){
        // session(null);
        session('login_flag', null);                
        session('uid', null);
        $this->redirect("User/login");
    }

    // 切换餐厅营业状态
    public function changeStatus(){
        $rid = 10086 + session('uid');

        $model = M('resturant');

        $update = $model->where("rid = $rid")->field('isOpen')->find();
        if ($update['isOpen']) {
            $update['isOpen'] = 0;
        }else{
            $update['isOpen'] = 1;
        }

        if ($model->where("rid = $rid")->setField($update)) {
            session('isOpen', $update['isOpen']);
        }

        // p($_SERVER);die;
        redirect($_SERVER['HTTP_REFERER']);//重定向至来源网页
    }
}