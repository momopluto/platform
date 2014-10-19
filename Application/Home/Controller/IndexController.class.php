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
    }
    

    // 主页
    public function index(){
        // p(session());die;
        $this->display();
    }

    // 退出
    public function quit(){
        session(null);
        $this->redirect("User/login");
    }
}