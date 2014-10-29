<?php
/**
 * Client公共函数
 *
 */

// 比较本月销售额(降序)，用于所有餐厅排序
function compare_month_sale($x, $y){
	if($x['month_sale'] == $y['month_sale']){//
		return 0;
	}elseif($x['month_sale'] > $y['month_sale']){
		return -1;
	}else{
		return 1;
	}
}


// 比较上个月销售额(降序)，用于所有餐厅排序
function compare_last_month_sale($x, $y){
	if($x['last_month_sale'] == $y['last_month_sale']){//
		return 0;
	}elseif($x['last_month_sale'] > $y['last_month_sale']){
		return -1;
	}else{
		return 1;
	}
}

// 订餐页面所需要的餐厅的信息，组装
function rstInfo_combine($an_rst){
	// 判断是否营业时间
    $n_time = date('H:i');


    if(strtotime($n_time) < strtotime($an_rst['stime_1_open'])){
        $open_status = "0";
    }elseif(strtotime($n_time) <= strtotime($an_rst['stime_1_close'])){
        $open_status = "1";
    }else{
        $open_status = "14";
    }

    if ($an_rst['stime_2_open'] !== '' && $an_rst['stime_2_close'] !== '') {
        $has_2_time = true;
        if(strtotime($n_time) < strtotime($an_rst['stime_2_open'])){
            if($open_status == "14"){
                $open_status = "12";
            }
        }elseif(strtotime($n_time) <= strtotime($an_rst['stime_2_close'])){
            $open_status = "2";
        }else{
            $open_status = "24";
        }
    }

    if ($an_rst['stime_3_open'] !== '' && $an_rst['stime_3_close'] !== '') {
        $has_2_time = true;
        if(strtotime($n_time) < strtotime($an_rst['stime_3_open'])){
            if($open_status == "24"){
                $open_status = "23";
            }
        }elseif(strtotime($n_time) <= strtotime($an_rst['stime_3_close'])){
            $open_status = "3";
        }else{
            $open_status = "4";
        }
    }

    $an_rst['open_status'] = $open_status;

    $an_rst['month_sale'] = M('menu', $an_rst['rid']."_")->sum('month_sale');//本月销售量
    $an_rst['last_month_sale'] = M('menu', $an_rst['rid']."_")->sum('last_month_sale');//本月销售量

    return $an_rst;
}


// 将open - close之间的时间，以10分钟为间隔分割
function cut_cut($open, $close, $on){

	$s_times = array();

	$strOpen = strtotime($open);
	$strClose = strtotime($close);

	if($on){//餐厅正在营业

		if(($strClose  - $strOpen) / 60 > 40){
			
			$strOpen = $strOpen + (600 - $strOpen % 600);//向上取整为10的倍数

			for ($i=2; $i <= ($strClose  - $strOpen) / 600; $i++) { 

				array_push($s_times, date('H:i',($strOpen + 600 * $i)));

			}
		}

	}else{//餐厅尚未营业

		for ($i=0; $i <= ($strClose  - $strOpen) / 600; $i++) { 

			array_push($s_times, date('H:i',($strOpen + 600 * $i)));

		}
	}

	return $s_times;
}

// 划分送餐时间，返回字符串数组
function cut_send_times($an_rst){

	$s_times = array();

	if(intval($an_rst['open_status']) % 10 != 4){//未到休息时间
		
		if($an_rst['open_status'] == "0"){

			$s_times ＝ cut_cut($an_rst['stime_1_open'], $an_rst['stime_1_close'], 0);

		}elseif($an_rst['open_status'] == "1"){

			$s_times ＝ cut_cut(date('H:i'), $an_rst['stime_1_close'], 1);

		}elseif($an_rst['open_status'] == "12"){

			$s_times ＝ cut_cut($an_rst['stime_2_open'], $an_rst['stime_2_close'], 0);

		}elseif($an_rst['open_status'] == "2"){

			$s_times ＝ cut_cut(date('H:i'), $an_rst['stime_2_close'], 1);

		}elseif($an_rst['open_status'] == "23"){

			$s_times ＝ cut_cut($an_rst['stime_3_open'], $an_rst['stime_3_close'], 0);

		}elseif($an_rst['open_status'] == "3"){

			$s_times ＝ cut_cut(date('H:i'), $an_rst['stime_3_close'], 1);

		}

	}

	return $s_times;
}


?>