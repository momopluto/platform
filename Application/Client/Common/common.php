<?php
/**
 * Client公共函数
 *
 */

// 比较销售额
function compare_sales($x, $y){
	if($x['Description'] == $y['Description']){//
		return 0;
	}elseif($x['Description'] > $y['Description']){
		return 1;
	}else{
		return -1;
	}
}



?>