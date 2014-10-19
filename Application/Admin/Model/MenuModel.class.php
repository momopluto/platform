<?php
namespace Admin\Model;
use Think\Model;
/**
 * 自定义菜单模型
 *
 */
class MenuModel extends Model{

    protected $_validate = array(
        array('sort','require','排序号不能为空！'), //默认情况下用正则进行验证
        array('title','require','菜单名不能为空！'), //默认情况下用正则进行验证
    );
}

?>