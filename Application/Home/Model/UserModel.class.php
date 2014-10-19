<?php
namespace Home\Model;
use Think\Model;

/**
 * 餐厅用户账号模型
 *
 */
class UserModel extends Model {

    protected $_validate = array(
        array('username','require','用户名不能为空！',1),
        array('password','require','密码不能为空！',1),
        array('repassword','require','确认密码为空！'),
        array('username','','用户名已经存在！',1,'unique',1),

        array('password', '6,18','密码长度不正确！应为6-18位',1, length),
        array('password', 'repassword','2次密码输入不一致！',0, confirm),
    );

    protected $_auto = array (

        array('password','md5',3,'function') , // 对password字段在新增和编辑的时候使md5函数处理

        // array('update_time','time',2,'function'), // 修改密码时，更新update_time

        array('reg_time','time',1,'function'), // 新增注册用户，写入当前时间戳
        array('reg_ip','get_client_ip',1,'function'),//新增注册用户，写入注册ip
    );
}

?>