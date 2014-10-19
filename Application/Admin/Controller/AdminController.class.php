<?php

namespace Admin\Controller;
use Think\Controller;

/**
 * 后台公共控制器
 * 
 */
class AdminController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		// redirect("http://www.qq.com/404");
	}

	//初始化操作
	function _initialize() {
		//不允许前台直接访问？
		
		/* 管理员登录检测 */
		if(!is_admin_login()){
			session(null);
			$this->error('您还没有登录，请先登录！', U('User/login'));
		}
    }
}
