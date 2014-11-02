<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 餐厅用户管理
 *
 * 账号为餐厅手机
 */
class RstuserController extends AdminController {
    public function _initialize() {
        parent::_initialize ();

        /* 是否有设置公众号 */
        has_token() || $this->error('您未设置默认公众号，请先设置！', U('User/info'));
    }

	// 餐厅列表
	public function lists(){
		//1.得到admin_allrst内所有餐厅的rid、s_change_time、status信息，$temp
		//2.通过rid，得到home_resturant表中对应的餐厅信息，$an_rst，字段logo_url、rst_name、rst_address、rst_phone
		//3.组合

		//遍历$temp，得$one_data['rid']
		//		通过$one_data['rid']得到$an_rst
		//		组合$one_data和$an_rst
		//结果放入二维数组$data

		//组合餐厅信息
        $data = array();
        $map['token'] = session('token');
        $temp = M('allrst')->where($map)->select();
        foreach($temp as $one_data){
            $rid = $one_data['rid'];
            $an_rst = M('resturant','home_')->where("rid = $rid")->field('logo_url,rst_name,rst_address,rst_phone')->find();
            //组合$one_data和$an_rst
            if($an_rst){
            	$data[] = $one_data + $an_rst;
            }
        }
        // p($data);die;

		$this->assign('data', $data);
		$this->display();
	}

/* 以下方法修饰符，如何改为private后还能访问？ */
	
	// 18826481053
	// ee2b584ba340

	// 生成新用户账号密码
	public function newUser(){
		if(IS_POST){
			$data = I('post.');
			if(strlen($data['password']) < 6 || strlen($data['password']) > 18){
				$this->error('密码应为6-18位字符！');
			}

			$arr['username'] = $data['username'];
			$arr['password'] = md5($data['password']);
			$arr['reg_time'] = NOW_TIME;
			$arr['reg_ip'] = get_client_ip();
			$arr['update_time'] = $arr['reg_time'];
			$arr['token'] = session('token');
			// p($arr);die;

			// 新增home_user数据
			$hm_user = M('user','home_');
			if($hm_user->where(array('username' => $arr['username']))->count()){
				$this->error('该账号已存在！',U('Admin/Rstuser/newUser'));
			}
			// p($hm_user->select());die;
			if($hm_user->create($arr) && $id=$hm_user->add($arr)){//生成账号成功
				//admin_allrst中添加餐厅数据
				$rid = 10086 + $id;
				$rst['rid'] = $rid;
				$rst['s_change_time'] = NOW_TIME;
				$rst['token'] = session('token');
				// 关联admin_allrst
				$adm_rst = M('allrst');
				$adm_rst->create($rst) && $adm_rst->add($rst);//生成关联


				//生成餐厅菜单数据库_menu、今日订单序数据库_today
				$cn = mysql_connect('localhost','root','bitnami');
				mysql_select_db('pltf',$cn);
				
				$sql ="CREATE TABLE `".$rid."_menu` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `pid` tinyint(2) NOT NULL DEFAULT '0',
				  `name` varchar(255) NOT NULL,
				  `price` float(6,0) unsigned NOT NULL,
				  `description` tinytext,
				  `stock` int(10) unsigned NOT NULL DEFAULT '100000',
				  `tag` smallint(4) unsigned zerofill NOT NULL DEFAULT '0000',
				  `sort` smallint(4) unsigned NOT NULL DEFAULT '0',
				  `month_sale` int(10) unsigned NOT NULL DEFAULT '0',
				  `last_month_sale` int(10) unsigned NOT NULL DEFAULT '0',
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;";
				mysql_query($sql);//创建rid_menu

				$sql = "CREATE TABLE `".$rid."_today` (
				  `today_sort` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `rid` int(10) NOT NULL,
				  PRIMARY KEY (`today_sort`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
				mysql_query($sql);//创建rid_today

				mysql_close($cn);
				// die;

				$this->assign('data', $data);
				$this->display("showUser");
			}else{//失败
				$this->error($hm_user->getError());
			}
		}else{
			$this->display();
		}
	}

	// 重置用户账号的密码
	public function resetPsw(){
		//通过编号id得到餐厅rid
		//通过餐厅rid-10086得餐厅id
		//随机重置一个新密码，display
		//确认更新，'password'和'update_time'
		$map['token'] = session('token');

		$hm_user = M('user','home_');
		if(IS_POST){
			$data = I('post.');
			// p($data);
			if(strlen($data['password']) < 6 || strlen($data['password']) > 18){
				$this->error('密码应为6-18位字符！');
			}

			$update['password'] = md5($data['password']);
			$update['update_time'] = NOW_TIME;

			if($hm_user->where(array('username' => $data['username']))->setField($update)){
				$this->assign('data', $data);
				$this->display("showUser");
			}else{
				$this->error('密码重置失败！');
			}
		}else{
			$map['id'] = I('get.id');
			$an_rst = M('allrst')->where($map)->find();
			$rid = $an_rst['rid'];
			$uid = $rid - 10086;//$uid为用户账号id

			$user_data = $hm_user->where("id = $uid")->field('username')->find();

			$this->assign('data', $user_data);
			$this->display();
		}		
	}

	// 平台服务(账号)开启/停用
	public function changeService(){
		$map['token'] = session('token');
		$map['id'] = I('get.id');
		$data = M('allrst')->where($map)->find();
		// p($data);die;

		if($data['status']){
			$update['status'] = 0;
		}else{
			$update['status'] = 1;
		}
		$update['s_change_time'] = NOW_TIME;

		// p($update);die;
		M('allrst')->where($map)->setField($update);

		unset($update['s_change_time']);

		$uid = $data['rid'] - 10086;//$uid为用户账号id
		M('user', 'home_')->where("id = $uid")->setField($update);

		redirect(U('Admin/Rstuser/lists'));
	}


	// 查看该商家运营情况
	public function statistics(){
		$id = I('get.id');

		// allrst餐厅表中得到餐厅的rid
		$whe['token'] = session('token');
		$whe['id'] = $id;
        $temp = M('allrst')->where($whe)->find();
        $rid = $temp['rid'];


        $today = date('Y-m-d');//今日
        $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳
        $last_month_days = getLastMonth_StartAndEnd($today);//上月第1日和最后1日，数组时间戳
        

        //餐厅的营业情况统计数组
    	$one_data = array(
			"total_turnover"=>0,"blanket_order"=>0,"1_order"=>0,"0_order"=>0,
			"last_month_turnover"=>0,"last_month_blanket_order"=>0,"last_month_1_order"=>0,"last_month_0_order"=>0,
			"month_turnover"=>0,"month_blanket_order"=>0,"month_1_order"=>0,"month_0_order"=>0,
			"today_turnover"=>0,"today_blanket_order"=>0,"today_1_order"=>0,"today_0_order"=>0,
		);

    	$map['rid'] = $rid;
    	$orders = M('orderitem',' ')->where($map)->order('cTime desc')->select();//当前餐厅所有订单信息
    	$count = count($orders);

    	foreach ($orders as $an_order) {
    		if(date('Y-m-d', $an_order['cTime']) == $today){//今日订单统计
    			// echo "+今日+";
    			$one_data['today_blanket_order'] ++ ;

        		if($an_order['status'] != 0 && $an_order['status'] != 3){//排除0未确认和3无效订单
        			$one_data['today_turnover'] += $an_order['total'];//今日总金额
			        $one_data['today_1_order'] ++;//今日有效订单数
        		}elseif($an_order['status'] == 3){
        			$one_data['today_0_order'] ++;//今日无效订单数
        		}
    		}

    		if($month_days[0] <= $an_order['cTime'] && $an_order['cTime'] <= $month_days[1]){//本月订单
    			// echo "+本月+";
    			$one_data['month_blanket_order'] ++ ;
    			
        		if($an_order['status'] != 0 && $an_order['status'] != 3){//排除0未确认和3无效订单
        			$one_data['month_turnover'] += $an_order['total'];//本月总金额
			        $one_data['month_1_order'] ++;//本月有效订单数
        		}elseif($an_order['status'] == 3){
        			$one_data['month_0_order'] ++;//本月无效订单数
        		}
    		}elseif($last_month_days[0] <= $an_order['cTime'] && $an_order['cTime'] <= $last_month_days[1]){//上月订单
				// echo "+上月+";
				$one_data['last_month_blanket_order'] ++ ;
    			
        		if($an_order['status'] != 0 && $an_order['status'] != 3){//排除0未确认和3无效订单
        			$one_data['last_month_turnover'] += $an_order['total'];//总金额
			        $one_data['last_month_1_order'] ++;//上月有效订单数
        		}elseif($an_order['status'] == 3){
        			$one_data['last_month_0_order'] ++;//上月无效订单数
        		}
    		}

    		//总统计
    		$one_data['blanket_order'] ++ ;
    		if($an_order['status'] != 0 && $an_order['status'] != 3){//排除0未确认和3无效订单
    			$one_data['total_turnover'] += $an_order['total'];//总金额
		        $one_data['1_order'] ++;//总有效订单数
    		}elseif($an_order['status'] == 3){
    			$one_data['0_order'] ++;//总无效订单数
    		}
    	}

        // p($one_data);die;

        $this->assign('data', $one_data);
        $this->display();
	}

	// 所有商家运营情况
	public function all_statistics(){

		// 1.统计每个餐厅的营业情况
		// 2.汇总所有平台中的商家的营业情况
		// 
		// 对于1，首先要知道每个餐厅的rid(微信号token为必带的过滤字段)，然后对应id统计运营信息
		// 实际流程：
		// A.读取allrst中token对应的所有启用了平台服务的餐厅信息$rsts[读1次allrst表]
		// B.循环$rsts，rid = $rsts['rid']
		// C.根据rid过滤出该餐厅的订单信息[读1次orderitem取出该餐厅所有订单]
		// D.统计订单情况[根据条件，循环所得餐厅订单数组]
		// E.组合成包含多个餐厅的数组$datas
		// F.汇总$datas成$all
		
		$whe['token'] = session('token');
		$whe['status'] = 1;//启用平台服务
        $rsts = M('allrst')->where($whe)->select();


        $model = M('orderitem',' ');//订单模型
        $today = date('Y-m-d');//今日
        $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳
        $last_month_days = getLastMonth_StartAndEnd($today);//上月第1日和最后1日，数组时间戳

        foreach ($rsts as $one_rst) {
        	//每个餐厅的营业情况统计数组
        	$one_data = array(
    			"total_turnover"=>0,"blanket_order"=>0,"1_order"=>0,"0_order"=>0,
    			"last_month_turnover"=>0,"last_month_blanket_order"=>0,"last_month_1_order"=>0,"last_month_0_order"=>0,
    			"month_turnover"=>0,"month_blanket_order"=>0,"month_1_order"=>0,"month_0_order"=>0,
    			"today_turnover"=>0,"today_blanket_order"=>0,"today_1_order"=>0,"today_0_order"=>0,
    		);

        	// echo $one_rst['rid']."<br/>";
        	$map['rid'] = array('eq',$one_rst['rid']);
        	$orders = $model->where($map)->order('cTime desc')->select();//当前餐厅所有订单信息
        	$count = count($orders);
        	// echo "<hr>$count";

        	foreach ($orders as $an_order) {
        		if(date('Y-m-d', $an_order['cTime']) == $today){//今日订单统计
        			// echo "+今日+";
        			$one_data['today_blanket_order'] ++ ;

	        		if($an_order['status'] != 0 && $an_order['status'] != 3){//排除0未确认和3无效订单
	        			$one_data['today_turnover'] += $an_order['total'];//今日总金额
				        $one_data['today_1_order'] ++;//今日有效订单数
	        		}elseif($an_order['status'] == 3){
	        			$one_data['today_0_order'] ++;//今日无效订单数
	        		}
        		}

        		if($month_days[0] <= $an_order['cTime'] && $an_order['cTime'] <= $month_days[1]){//本月订单
        			// echo "+本月+";
        			$one_data['month_blanket_order'] ++ ;
        			
	        		if($an_order['status'] != 0 && $an_order['status'] != 3){//排除0未确认和3无效订单
	        			$one_data['month_turnover'] += $an_order['total'];//本月总金额
				        $one_data['month_1_order'] ++;//本月有效订单数
	        		}elseif($an_order['status'] == 3){
	        			$one_data['month_0_order'] ++;//本月无效订单数
	        		}
        		}elseif($last_month_days[0] <= $an_order['cTime'] && $an_order['cTime'] <= $last_month_days[1]){//上月订单
					// echo "+上月+";
					$one_data['last_month_blanket_order'] ++ ;
        			
	        		if($an_order['status'] != 0 && $an_order['status'] != 3){//排除0未确认和3无效订单
	        			$one_data['last_month_turnover'] += $an_order['total'];//总金额
				        $one_data['last_month_1_order'] ++;//上月有效订单数
	        		}elseif($an_order['status'] == 3){
	        			$one_data['last_month_0_order'] ++;//上月无效订单数
	        		}
        		}

        		//总统计
        		$one_data['blanket_order'] ++ ;
        		if($an_order['status'] != 0 && $an_order['status'] != 3){//排除0未确认和3无效订单
        			$one_data['total_turnover'] += $an_order['total'];//总金额
			        $one_data['1_order'] ++;//总有效订单数
        		}elseif($an_order['status'] == 3){
        			$one_data['0_order'] ++;//总无效订单数
        		}
        	}
        	// p($one_data);
        	$datas[$one_rst['rst_name']] = $one_data;//多个餐厅的营业情况数组，键为$rid
        }


        $all = array(
			"total_turnover"=>0,"blanket_order"=>0,"1_order"=>0,"0_order"=>0,
			"last_month_turnover"=>0,"last_month_blanket_order"=>0,"last_month_1_order"=>0,"last_month_0_order"=>0,
			"month_turnover"=>0,"month_blanket_order"=>0,"month_1_order"=>0,"month_0_order"=>0,
			"today_turnover"=>0,"today_blanket_order"=>0,"today_1_order"=>0,"today_0_order"=>0,
    	);
        foreach ($datas as $data) {
        	$all['total_turnover'] += $data['total_turnover'];
	        $all['blanket_order'] += $data['blanket_order'];
	        $all['1_order'] += $data['1_order'];
	        $all['0_order'] += $data['0_order'];
	        $all['last_month_turnover'] += $data['last_month_turnover'];
	        $all['last_month_blanket_order'] += $data['last_month_blanket_order'];
	        $all['last_month_1_order'] += $data['last_month_1_order'];
	        $all['last_month_0_order'] += $data['last_month_0_order'];
	        $all['month_turnover'] += $data['month_turnover'];
	        $all['month_blanket_order'] += $data['month_blanket_order'];
	        $all['month_1_order'] += $data['month_1_order'];
	        $all['month_0_order'] += $data['month_0_order'];
	        $all['today_turnover'] += $data['today_turnover'];
	        $all['today_blanket_order'] += $data['today_blanket_order'];
	        $all['today_1_order'] += $data['today_1_order'];
	        $all['today_0_order'] += $data['today_0_order'];
        }
        // p($datas);
        // p($all);
        // die;

        $this->assign('datas', $datas);//多个餐厅，每个餐厅的情况
        $this->assign('all', $all);//所有餐厅，汇总
        $this->display();
	}
}

?>