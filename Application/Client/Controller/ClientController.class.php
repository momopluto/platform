<?php

namespace Client\Controller;
use Think\Controller;

/**
 * 订餐用户公共控制器
 * 
 */
class ClientController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		// redirect("http://www.qq.com/404");
		redirect(U('Client/Restaurant/lists'));
	}

	//初始化操作
	function _initialize() {
		// /* 用户登录检测 */
		//  if(!is_login()){
		// 	session(null);
		// 	$this->error('您还没有登录，请先登录！', U('User/login'));
		// }
    }

}
?>