<?php
namespace Admin\Controller;
use Think\Controller;


/**
 * 自定义菜单
 *
 */
class MenuController extends AdminController {
    protected $model;
    public function _initialize() {
        parent::_initialize ();

        /* 是否有设置公众号 */
        has_token() || $this->error('您未设置默认公众号，请先设置！', U('User/info'));

        
        $this->model = D ('menu')->order('pid, sort');
    }

    //菜单列表
    public function lists(){

        // 最多18条数据，不分页
        $data = $this->format_menu();

        $this->assign('data',$data);
        $this->display();
    }

    // 增加菜单
    public function add(){
        $map['token'] = session('token');

        if(IS_POST){
            $data = I('post.');            
            if($data['keyword'] =='' && $data['url'] == ''){
                $this->error('关联关键词 和 关联URL 至少填写1个！');
            }
            $data['keyword'] = trim($data['keyword']);
            $data['token'] = session('token');

            if($this->model->where($map)->create($data) && $this->model->add($data)){
                $this->success('添加成功',U('Admin/Menu/lists'));
            }else{
                $this->error($this->model->getError());
            }

        }else{
            $temp = $this->model->where($map)->where("pid = 0")->select();
            $pids = array();
            foreach($temp as $t){
                $pids["$t[id]"] = $t['title'];
            }

            $this->assign('pids', $pids);
            $this->display();
        }
    }

    // 编辑菜单
    public function edit(){

        $map['token'] = session('token');

        if(IS_POST){
            $data = I('post.');
            
            $data['token'] = session('token');//create中用到
            if($this->model->where($map)->create($data) === false){
                $this->error($this->model->getError());
            }

            if($data['keyword'] =='' && $data['url'] == ''){
                $this->error('关联关键词 和 关联URL 至少填写1个！');
            }

            $map['id'] = I('get.id');
            if($this->model->where($map)->save($data) !==false){
                $this->success('修改成功',U('Admin/Menu/lists'));
            }else{
                $this->error('修改失败');
            }
        }else{

            $temp = $this->model->where($map)->where("pid = 0")->select();
            $pids = array();
            foreach($temp as $t){
                $pids["$t[id]"] = $t['title'];
            }

            $map['id'] = I('get.id');
            $data = $this->model->where($map)->find();
            $this->assign('data', $data);
            $this->assign('pids', $pids);

            $this->display();
        }
    }

    // 删除菜单
    public function del(){

        $map['token'] = session('token');
        $map['id'] = I('get.id');

        if($this->model->where($map)->delete()){
            $this->success('删除成功',U('Admin/Menu/lists'));
        }else{
            $this->error('删除失败');
        }
    }

    // 发送菜单到微信
    public function send_menu() {
//        $data = $this->get_data ();
        $data = $this->format_menu();
        foreach ( $data as $k => $d ) {//构造主菜单
            if ($d ['pid'] != 0)
                continue;
            $tree ['button'] [$d ['id']] = $this->_deal_data ( $d );
            unset ( $data [$k] );
        }
        foreach ( $data as $k => $d ) {//构造子菜单
            $tree ['button'] [$d ['pid']] ['sub_button'] [] = $this->_deal_data ( $d );
            unset ( $data [$k] );
        }
        $tree2 = array ();
        $tree2 ['button'] = array ();

        foreach ( $tree ['button'] as $k => $d ) {//将主菜单编号由原来的id改成1、2、3，菜单数组构造完成
            $tree2 ['button'] [] = $d;
        }

        $tree = $this->json_encode_cn ( $tree2 );
        //获取access_token,判断有无缓存
        $access_token = get_access_token();

        file_get_contents ( 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $access ['access_token'] );

        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
        $header [] = "content-type: application/x-www-form-urlencoded; charset=UTF-8";

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $tree );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        $res = curl_exec ( $ch );
        curl_close ( $ch );
        $res = json_decode ( $res, true );
        if ($res ['errcode'] == 0) {
            $this->success ( '发送菜单成功' );
        } else {
            $this->success ( '发送菜单失败，错误的返回码是：' . $res ['errcode'] . ', 错误的提示是：' . $res ['errmsg'] );
        }
    }

/*=======================================格式化菜单 begin====================================*/
    //将菜单处理成层次结构，我的方法
    private function format_menu(){
        $temp = $this->model->where("pid = 0")->select();
        $data = array();
        $map['token'] = session('token');
        foreach($temp as $one_data){
            $id = $one_data['id'];
            $data[] = $one_data;
            
            $map['pid'] = $id;
            $childs = $this->model->where($map)->order('sort')->select();
            foreach($childs as $child){
                $data[] = $child;
            }
        }
        return $data;
    }

    //将菜单处理成层次结构，weiphp的方法
    private function get_data($map = null) {
        $map ['token'] = get_token ();
        $list = M ( 'menu' )->where ( $map )->order ( 'pid asc, sort asc' )->select ();

        // 取一级菜单
        foreach ( $list as $k => $vo ) {
            if ($vo ['pid'] != 0)
                continue;

            $one_arr [$vo ['id']] = $vo;
            unset ( $list [$k] );
        }

        foreach ( $one_arr as $p ) {
            $data [] = $p;

            $two_arr = array ();
            foreach ( $list as $key => $l ) {
                if ($l ['pid'] != $p ['id'])
                    continue;

                $l ['title'] = '├──' . $l ['title'];
                $two_arr [] = $l;
                unset ( $list [$key] );
            }

            $data = array_merge ( $data, $two_arr );
        }

        return $data;
    }

    private function _deal_data($d) {
//        $res ['name'] = str_replace ( '├──', '', $d ['title'] );
        $res ['name'] = $d ['title'];

        if (! empty ( $d ['url'] )) {//url优先级 > keyword关键字
            $res ['type'] = 'view';
            $res ['url'] = $d ['url'];

        } else {
            $res ['type'] = 'click';
            $res ['key'] = $d ['keyword'];
        }
        return $res;
    }

    private function json_encode_cn($data) {
        $data = json_encode ( $data );
        return preg_replace ( "/\\\u([0-9a-f]{4})/ie", "iconv('UCS-2BE', 'UTF-8', pack('H*', '$1'));", $data );
    }
/*=======================================格式化菜单 end====================================*/
}

?>