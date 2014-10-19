<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 餐厅菜单
 * 
 */
class MenuController extends HomeController {

    function _initialize() {
        parent::_initialize ();

        $rid = 10086 + session('uid');
        $this->model = M ('menu', $rid."_")->order('pid, sort');
    }

    // 菜单列表
    function lists(){
        //pid=0的类别信息数组$category，按sort排序，其中的price为该类别下的菜式数量
        $category = $this->model->where("pid = 0")->select();

        //$data存放所有菜式，在表单内再循环遍历归类pid
        $data = array();
        // $map['token'] = session('token');
        foreach($category as $one_data){
            $id = $one_data['id'];
            // $data[] = $one_data;
            
            $map['pid'] = $id;
            $childs = $this->model->where($map)->order('sort')->select();
            foreach($childs as $child){
                $data[] = $child;
            }
        }

        $this->assign('category', $category);
        $this->assign('data', $data);
    	$this->display();
    }

    // 新增菜单
    function add_menu(){
        //菜名必须
        //价格非负，可为0
        //序号不填，默认为0        

        $pid = I('get.pid');
        if($new_menu = trim(I('post.new_menu'))){//菜单名不空
            $data['pid'] = $pid;
            $data['name'] = $new_menu;
            if(is_null(I('post.price'))){
                $data['price'] = 0;
            }elseif(I('post.price') >= 0){
                $data['price'] = I('post.price');
            }else{
                $this->error('价格为负？');
            }
            $data['description'] = trim(I('post.description'));
            if(is_null(I('post.sort'))){
                $data['sort'] = 0;
            }else{
                $data['sort'] = I('post.sort');
            }
            // 标签判断
            $tag = 0;
            $t = 0;
            if(I('post.is_new')){
                $t = 1;
            }else{
                $t = 0;
            }
            $tag = 10*$tag + $t;
            if(I('post.is_featured')){
                $t = 1;
            }else{
                $t = 0;
            }
            $tag = 10*$tag + $t;
            if(I('post.is_gum')){
                $t = 1;
            }else{
                $t = 0;
            }
            $tag = 10*$tag + $t;
            if(I('post.is_spicy')){
                $t = 1;
            }else{
                $t = 0;
            }
            $tag = 10*$tag + $t;
            $data['tag'] = $tag;

            // p($data);die;

            if($this->model->create($data) && $this->model->add($data)){
                $map['id'] = $pid;
                $this->model->where($map)->setInc('price');//所属类别的菜单数+1
                $this->success('新增菜单成功！');
            }else{
                $this->error($this->model->getError());
            }            
        }else{
            $this->error('菜单名不能为空！');
        }
    }    

    // 删除菜单
    function del_menu(){
        $map['id'] = I('get.id');
        if($this->model->where($map)->delete()){
            $map['id'] = I('get.pid');
            $this->model->where($map)->setDec('price');//所属类别的菜单数-1
            $this->success('删除菜单成功！');
        }else{
            $this->error('删除菜单失败！');
        }
    }

    // 菜单库存清零
    function stockclear(){
        $map['id'] = I('get.id');
        $this->model->where($map)->setField('stock', 0);
        redirect(U('Home/Menu/lists'));
    }

    // 编辑菜单
    function edit_menu(){
        p(I('post.'));die;

        if($name = trim(I('post.name'))){//菜单名不空
            $data['name'] = $name;
            if(is_null(I('post.price'))){
                $data['price'] = 0;
            }elseif(I('post.price') >= 0){
                $data['price'] = I('post.price');
            }else{
                $this->error('价格为负？');
            }
            $data['description'] = trim(I('post.description'));
            if(is_null(I('post.sort'))){
                $data['sort'] = 0;
            }else{
                $data['sort'] = I('post.sort');
            }
            if(is_null(I('post.stock'))){
                $data['stock'] = 100000;
            }else{
                $data['stock'] = I('post.stock');
            }

            // 标签判断
            $tag = 0;
            $t = 0;
            if(I('post.is_new')){
                $t = 1;
            }else{
                $t = 0;
            }
            $tag = 10*$tag + $t;
            if(I('post.is_featured')){
                $t = 1;
            }else{
                $t = 0;
            }
            $tag = 10*$tag + $t;
            if(I('post.is_gum')){
                $t = 1;
            }else{
                $t = 0;
            }
            $tag = 10*$tag + $t;
            if(I('post.is_spicy')){
                $t = 1;
            }else{
                $t = 0;
            }
            $tag = 10*$tag + $t;
            $data['tag'] = $tag;

            // p($data);die;

            $map['id'] = I('get.id');
            if($this->model->where($map)->save($data)){
                // $this->success('修改菜单成功！');
                redirect(U('Home/Menu/lists'));
            }else{
                $this->error($this->model->getError());
            }            
        }else{
            $this->error('菜单名不能为空！');
        }
    }

/*--------------------------------------------------------------*/

    // 新增分类
    function add_cate(){
        // p(I('post.'));die;
        //菜名必须
        //序号，默认为0 
        //分类描述，可不填

        if($new_cate = trim(I('post.new_cate'))){//类别名不空
            $data['pid'] = 0;
            $data['name'] = $new_cate;
            $data['price'] = 0;
            $data['description'] = trim(I('post.description'));
            if(is_null(I('post.sort'))){
                $data['sort'] = 0;
            }else{
                $data['sort'] = I('post.sort');
            }

            if($this->model->create($data) && $this->model->add($data)){
                $this->success('新增分类成功！');
            }else{
                $this->error($this->model->getError());
            }            
        }else{
            $this->error('分类名不能为空！');
        }
    }

    // 删除分类
    function del_cate(){
        $map['id'] = I('get.id');
        if($this->model->where($map)->delete()){
            $map2['pid'] = I('get.id');
            $this->model->where($map2)->delete();//删除该分类下的所有菜单
            $this->success('删除分类成功！');
        }else{
            $this->error('删除分类失败！');
        }
    }

    // 分类库存清零
    function setStockEmpty(){
        $map['pid'] = I('get.pid');
        $this->model->where($map)->setField('stock', 0);
        redirect(U('Home/Menu/lists'));
    }

    // 分类库存置满
    function setStockFull(){
        $map['pid'] = I('get.pid');
        $this->model->where($map)->setField('stock', 100000);
        redirect(U('Home/Menu/lists'));
    }

    // 编辑分类
    function edit_cate(){

    }
}

?>