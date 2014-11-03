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
        has_rstInfo() || $this->error('您未初始化设置餐厅信息，请先设置！', U('Shopmanage/set'));
    }
    

    // 主页
    public function index(){
        // p(session());die;
        $this->display();
    }

    // 退出
    public function quit(){
        // session(null);
        session('login_flag', null);                
        session('uid', null);
        $this->redirect("User/login");
    }
}