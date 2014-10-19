<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 粉丝管理
 *
 */

class FollowController extends AdminController{

    protected $model;
    public function _initialize() {
        parent::_initialize ();

        /* 是否有设置公众号 */
        has_token() || $this->error('您未设置默认公众号，请先设置！', U('User/info'));

        $this->model = M ('follow');
    }

    public function lists(){

        $map['token'] = session('token');

        //分页
        $count = $this->model->where($map)->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数(20)
        $show = $Page->show();// 分页显示输出
        $list = $this->model->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出

        $this->assign('data',$list);
        $this->display();
    }

    public function edit(){
        $map['token'] = session('token');
        $map['id'] = I('get.id');
        if(IS_POST){
            //p($_POST);die;
            if($this->model->where($map)->save($_POST) !== false){
                $this->success("修改成功",U('Admin/Follow/lists'),3);
            }else{
                $this->error("修改失败");
            }

        }else{
            // 获取数据
            $data = $this->model->where($map)->find();

            $this->assign ( 'data', $data );

            $this->display();
        }
    }

}

?>