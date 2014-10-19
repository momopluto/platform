<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * 后台主页
 *
 */
class IndexController extends AdminController {

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