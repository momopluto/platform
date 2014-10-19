<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 商家用户登录
 *
 * session内标记为uid和login_flag
 */
class UserController extends Controller {

    // 商家登录
    public function login(){
        
        if(IS_POST){
            $map['username'] = I('post.username');
            $map['password'] = md5(I('post.password'));

            // p($map);die;
            $user = M("user");
            $count = $user->where($map)->count();
            // p($user->where($map)->find());
            // echo $count;die;
            if($count){
                $an_data = $user->where($map)->find();
                if($an_data['status'] == 1){//账号启用
                    $arr['last_login_time'] = NOW_TIME;
                    $arr['last_login_ip'] = get_client_ip();
                    $user->where($map)->setField($arr);//更新上次登录时间和ip

                    session('login_flag', true);                
                    session('uid', $an_data['id']);
                    
                    $this->success('登录成功！',U("Home/Index/index"));
                }else{//账号未启用，$an_data['status'] == 0
                    $this->error("该账号未启用，请联系管理员！");
                }
                
            }else{
                session('login_flag', null);
                $this->error("密码错误！");
            }
        }else{
            $this->display();
        }
    }

    // 商家修改密码
    public function change_psw(){
        /* 用户登录检测 */
        is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
        
        if(IS_POST){
            // p(session());
            $map['password'] = md5(I('post.old_psw'));
            $map['id'] = session('uid');

            $vali['new_psw'] = I('post.new_psw');
            $vali['repsw'] = I('post.repsw');

            //判断新密码是否符合要求：6-18位
            if($vali['new_psw'] == ''){
                $this->error('密码不能为空！');
            }
            if(strlen($vali['new_psw']) < 6 || strlen($vali['new_psw']) > 18){
                $this->error('密码长度不符！应为6-18位');
            }

            $user = M('user');
            $count = $user->where($map)->count();
            if($count){
                if($vali['new_psw'] === $vali['repsw']){
                    $update['password'] = md5(I('post.new_psw'));
                    if($update['password'] === $map['password']){
                        $this->error('新旧密码不能相同！');
                    }
                    $update['update_time'] = NOW_TIME;
                    if($user->where($map)->save($update)){
                        $this->success('密码修改成功！',U('Home/Index/index'));    
                    }else{
                        $this->error($user->getError());
                    }
                }else{
                    $this->error('2次输入的新密码不一致！');
                }                
            }else{
                $this->error('原密码错误！');
            }
        }else{
            $this->display();
        }
    }
}

?>