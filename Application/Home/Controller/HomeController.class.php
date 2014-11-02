<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 
 */
class HomeController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		// redirect("http://www.qq.com/404");
		
	}

	//初始化操作
	function _initialize() {
		/* 用户登录检测 */
		 if(!is_login()){
			// session(null);
			session('login_flag', null);                
            session('uid', null);
			$this->error('您还没有登录，请先登录！', U('User/login'));
		}

		/* 限制一定要设置了餐厅信息才能进行其它操作 */
    }

}
?>