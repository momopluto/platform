<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 管理员后台登录
 *
 * session内标记为aid和admin_login_flag
 */
class UserController extends Controller {

    // 管理员登录
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

                $arr['last_login_time'] = NOW_TIME;
                $arr['last_login_ip'] = get_client_ip();
                $user->where($map)->setField($arr);//更新上次登录时间和ip

                // aid和admin_login_flag写入session
                session('admin_login_flag', true);
                $an_data = $user->where($map)->find();
                session('aid', $an_data['id']);

                // 将默认公众号token写入session
                if(!is_null($an_data['token'])){
                    session('token', $an_data['token']);
                }
                
                $this->success('登录成功！',U("Admin/Index/index"));
            }else{
                session('admin_login_flag', null);
                $this->error("密码错误！");
            }
        }else{
            $this->display();
        }
    }


    // //管理员注册（不开放）
    // public function reg(){
    //     if(IS_POST){
    //         $user = D("User");
    //         if($user->create()){
    //             unset($user->repassword);
    //             if($id = $user->add()){
    //                 $this->success("注册成功！",U("Admin/Index/index"));
    //             }
    //         }else{
    //             $this->error($user->getError());
    //         }
    //     }else{
    //         $this->display();    
    //     }
    // }


    // 管理员修改密码
    public function change_psw(){
        /* 管理员登录检测 */
        is_admin_login() || $this->error('您还没有登录，请先登录！', U('User/login'));

        if(IS_POST){
            // p(session());
            $map['password'] = md5(I('post.old_psw'));
            $map['id'] = session('aid');

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
                        $this->success('密码修改成功！',U('Admin/Index/index'));    
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

    protected $rules = array(
            // 公众号信息验证
            array('public_name','require','公众号名称不能为空！',1), //默认情况下用正则进行验证
            array('public_id','require','公众号原始id不能为空！', 1), //默认情况下用正则进行验证
            array('wechat','require','微信号不能为空！', 1), //默认情况下用正则进行验证
            array('appid','require','AppId不能为空！', 1), //默认情况下用正则进行验证
            array('secret','require','Secret不能为空！', 1), //默认情况下用正则进行验证

            array('public_id','','公众号原始id已存在！',0,'unique',1), // 在新增的时候验证public_id字段是否唯一  
        );

    // 绑定公众号
    public function info(){
        /* 管理员登录检测 */
        is_admin_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
        $id = session('aid');

        if(IS_POST){

            $update = I('post.');
            // p($update);
            $update['token'] = $update['public_id'];

            $model = M('user');
            if($model->validate($this->rules)->create($update) && $model->where("id = $id")->setField($update)){
                $this->success("绑定公众号成功！");
            }else{
                $this->error($model->getError());
            }
        }else{

            $data = M('user')->where("id = $id")->field('public_name, public_id, wechat, interface_url, headface_url, token, type, appid, secret')->find();
            // p($data);die;

            $this->assign('data', $data);
            $this->display();
        }
    }

}

?>