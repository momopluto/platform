<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 欢迎语
 *
 */
class WelcomeController extends AdminController {

    public function _initialize() {
        parent::_initialize ();
        
        /* 是否有设置公众号 */
        has_token() || $this->error('您未设置默认公众号，请先设置！', U('User/info'));
    }

    public function set(){
        $map['token'] = session('token');
        $data = M('welcome')->where($map)->find();
        if($data['type'] == 0){//文本欢迎语
            $this->text();
        }else{//$this->data['type'] == 1图文欢迎语
            $this->news();
        }
    }

    //文本欢迎语
    public function text(){
        $map['token'] = session('token');
        $model = M ('welcome');

        if(IS_POST){
            if($model->where($map)->setField(I('post.'))){
                $this->success("设置成功");
            }else{
                $this->error($model->getError());
            }
        }else{
            $data = $model->where($map)->find();
            $this->assign('data',$data );
            $this->display("text");
        }
    }

    //图文欢迎语
    public function news(){
        $map['token'] = session('token');
        $model = M ('welcome');

        if(IS_POST){
            $data = I('post.');
            $data['type'] = 1;

            if($model->where($map)->setField($data)){
                $this->success("设置成功");
            }else{
                $this->error($model->getError());
            }
        }else{
            $data = $model->where($map)->find();
            $this->assign('data',$data );
            $this->display("news");
        }
    }
}

?>