<?php
namespace Home\Model;
use Think\Model;

/**
 * 餐厅用户模型
 *
 */
class ResturantModel extends Model {

    protected $_validate = array(
        array('rst_name','require','餐厅名不能为空！',1),
        array('rst_address','require','餐厅地址不能为空！',1),
        array('rst_description','require','餐厅简介不能为空！'),
        array('rst_phone','require','联系电话不能为空！',1),
        array('rst_promotion_info','require','餐厅公告信息不能为空！',1),
        array('rst_agent_fee','require','外加配送费不能为空！'),
        array('rst_deliver_description','require','起送说明不能为空！',1),
        array('stime_1_open','require','第一营业时间不能为空！',1),
        array('stime_1_close','require','第一营业时间不能为空！'),
    );


    protected $_auto = array (
        
    );
	
}

?>