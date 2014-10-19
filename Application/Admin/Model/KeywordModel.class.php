<?php
namespace Admin\Model;
use Think\Model;
/**
 * 综合关键字表模型
 *
 */
class KeywordModel extends Model{

    protected $_validate = array(
        array('keyword','require','关键字不能为空！'), //默认情况下用正则进行验证
        array('sort','require','排序号不能为空！'), //默认情况下用正则进行验证

        array('keyword','','关键字完全重复！',0,'unique',1), // 在新增的时候验证keyword字段是否唯一
    );
}

?>