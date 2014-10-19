<?php
namespace Admin\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-9
 * Time: 上午10:41
 */

class OrdermanModel extends Model{

    protected $_validate = array(
        array('receiver_phone','require','手机号不能为空！'), //默认情况下用正则进行验证
        array('takeout_address','require','送餐地址不能为空！'), //默认情况下用正则进行验证

        array('receiver_phone','number','手机号只能为数字！'),
        array('receiver_phone','11','手机号长度不符！',3,'length'),
    );
}

?>