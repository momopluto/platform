<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 关键词控制器
 * 
 */
class KeywordController extends AdminController {

    public function _initialize() {
        parent::_initialize ();

        /* 是否有设置公众号 */
        has_token() || $this->error('您未设置默认公众号，请先设置！', U('User/info'));
    }


    /**
     * 分页方法
     * @param $model 数据模型
     * @return $list 要显示的数据
     */
    protected function Pagination($model){
        $map['token'] = session('token');

        $count = $model->where($map)->order('id desc')->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(5)
        $show = $Page->show();// 分页显示输出

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $model->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出

        return $list;
    }

    // 文本回复
    public function replytext_lists(){

        $model = M ('keyword_text');

        $data = $this->Pagination($model);
        $this->assign('data',$data);
        $this->assign('source', 'text');

        $this->display('Keyword/text/lists');
    }

    // 图文回复
    public function replynews_lists(){

        $model = M ('keyword_news');

        $data = $this->Pagination($model);
        $this->assign('data',$data);
        $this->assign('source', 'news');

        $this->display('Keyword/news/lists');
    }

    // 多图文回复
    public function replymult_lists(){

        $model = M ('keyword_mult');

        $data = $this->Pagination($model);

        //提取mult_ids对应的title
        foreach ( $data as &$one_data ) {
            $map ['id'] = array (
                'in',
                $one_data ['mult_ids']
            );
            $list = M ( 'keyword_news' )->field ( 'title' )->where ( $map )->select ();
            //p($list);
            foreach($list as $li){
                $res[] = $li['title'];
            }
            $one_data ['title'] = implode ( '<br/>', $res );
            //p($one_data);
        }

        $this->assign('data',$data);
        $this->assign('source', 'mult');

        $this->display('Keyword/mult/lists');
    }

    // 关键词维护列表
    public function maintain_lists(){
        $map['token'] = session('token');
        $model = M ('keyword')->where($map)->order('id desc');

        $data = $this->Pagination($model);
        $this->assign('data',$data);

        $this->display('Keyword/maintain/lists');
    }

    // 关键词删除
    public function maintain_del(){
        $id = I('get.id');

        $map['token'] = session('token');
        $map['id'] = $id;
        $data = M('keyword')->where($map)->field('from_id,extra_text')->find();
        
        if(M('keyword')->where($map)->delete() === false){
            // echo 综合表删除失败;
            $this->error("删除失败！");
        }

        $map['id'] = $data['from_id'];
        if(M($data['extra_text'])->where($map)->delete() === false){
            //3个表其中1个数据删除
            // echo 单表删除失败;
            $this->error("单表删除失败！");
        }else{
            $this->success("删除成功！",U('Admin/Keyword/maintain_lists'));
        }
    }

    //此处为3个关键词表的动态验证规则
    protected $rule = array(//验证规则
        array('keyword','require','关键字不能为空！'), //默认情况下用正则进行验证
        // array('content','require','回复内容不能为空！'), //默认情况下用正则进行验证
        array('sort','require','排序号不能为空！'), //默认情况下用正则进行验证
        array('title','require','标题不能为空！'), //默认情况下用正则进行验证

        array('keyword','','关键字完全重复！',0,'unique',1), // 在新增的时候验证keyword字段是否唯一
    );

    // 新增回复
    public function reply_add(){
        //$this->add_text($_SESSION);die;
        if(IS_POST){//提交表单，更新数据库

            $source = I('get.source');
            $post = I('post.');
            $post['keyword'] = ' '.trim($post['keyword']).' ';
            $post['token'] = session('token');

            // p($post);//最终要写入的数据，3个关键词表中的1个
            //die;

            $map['token'] = session('token');

            $model = D ("keyword_".$source);
            if($model->where($map)->validate($this->rule)->create($post) && ($from_id = $model->add($post))){//此处用与KeywordModel模型一样的规则验证

                //TODO，图文回复，标题不能为空没有限制

                //前提是关键词不完全相等，根据 token 和 from_id 惟一确定一条数据
                //更新综合关键词表keyword
                $data = array(
                    'keyword' => $post['keyword'],
                    'token' => $post['token'],
                    'from_id' => $from_id,
                    'extra_text' => "keyword_".$source,
                    'cTime' => NOW_TIME,
                    'keyword_length' => strlen($post['keyword']),
                    'keyword_type' => $post['keyword_type'],
                    'sort' => $post['sort'],
                );
                //KeywordModel.class.php中在验证规则在此有效（因为keyword表名对应KeywordModel.class.php模型，即模型是表名的首字母大写）
                $all_model = D('keyword');
                if($all_model->where($map)->create($data) && $all_model->add($data)){
                    $this->success("添加成功",U('Admin/Keyword/reply' . $source . "_lists"));
                }else{
                    // echo 综合关键词表;
                    $this->error($all_model->getError());
                }
            }else{
                // echo "keyword_" . $source ."关键词表";
                $this->error($model->getError());
            }
        }else{//进入表单填写

            $source = I('get.source');
            $this->assign('source', $source);
            $this->display('Keyword/' .$source .'/add');
        }
    }

    // 回复编辑
    public function reply_edit(){
        $id = I('get.id');
        $source = I('get.source');

        if(IS_POST){//提交表单，更新数据库

            $post = I('post.');

            // 表单内容不采用自动验证，在此逐一验证：keyword不能为空(但不限制重复，管理员自己控制)，排序号不为空
            if($post['keyword'] == ''){
                $this->error('关键字不能为空！');
            }
            if($post['sort'] == ''){
                $this->error('排序号不能为空！');
            }


            $post['keyword'] = ' '.trim($post['keyword']).' ';

            $map1['token'] = session('token');
            $map1['from_id'] = $id;
            
            $all_model = D('keyword');

            //更新综合关键词表keyword
            $data = array(
                'keyword' => $post['keyword'],
                'keyword_length' => strlen($post['keyword'])-2,
                'keyword_type' => $post['keyword_type'],
                'extra_text' => "keyword_".$source
            );

            //KeywordModel.class.php中在验证规则在此有效（因为keyword表名对应KeywordModel.class.php模型，即模型是表名的首字母大写）
            //此步骤不可缺少，用于表单验证，如果错误则生成验证信息
            //此处验证关键词是否完全重复
            // if(!$all_model->where($map1)->create($data)){
            //     $this->error($all_model->getError());
            // }

            //TODO，总结：未修改表单就提交，create方法会产生“关键字重复错误”，但我们不希望这种情况被报错;
            //TODO，于是，先将错误信息保存下来（$err标记），save方法不会将未修改的表单的关键字认为是重复，故能更新数据库（但有个问题：关键字为空也写入了）,而修改为冲突的关键字返回false
            //TODO，最后，妥协，还是直接验证到有错误就输出并跳转（不修改表单则会与自己冲突）  【留待js解决】


            if($all_model->where($map1)->save($data) === false){
                    $this->error("修改失败");
            }

            $map2['token'] = session('token');
            $map2['id'] = $id;
            //更新3个关键词表中的其中1个
            $model = D ("keyword_".$source);
            if($model->where($map2)->save($_POST) === false){
                $this->error("修改失败");
            }else{
                $this->success("修改成功",U('Admin/Keyword/reply' . $source . "_lists"));
            }
        }else{//进入编辑界面

            $map['token'] = session('token');
            $map['id'] = $id;
            //关键词所在数据库keyword_text(/news/mult)
            $data = M ("keyword_".$source)->where($map)->find();

            $this->assign('data',$data);
            $this->assign('id',$id);
            $this->assign('source', $source);
            $this->display('Keyword/' .$source .'/edit');
        }
    }

    // 回复删除
    public function reply_del(){

        $id = I('get.id');
        $source = I('get.source');

        $map1['token'] = session('token');
        $map1['from_id'] = $id;
        $map1['extra_text'] = "keyword_".$source;
        //删除综合关键词表keyword中数据
        if(D('keyword')->where($map1)->delete() === false){
            $this->error("删除失败");
        }

        $map2['token'] = session('token');
        $map2['id'] = $id;
        //删除关键词所在数据库keyword_text(/news/mult)数据
        $one_model = D ("keyword_".$source);
        if($one_model->where($map2)->delete() !== false){
            $this->success("删除成功",U('Admin/Keyword/reply'. $source . "_lists"));
        }else{
            $this->error("删除失败");
        }
    }

/*
    //关键词匹配测试
    private function test_keyword(){
        $keywords = array(
            //'2',
            'testtestt',
        );
        foreach($keywords as $keyword){
            $map['token'] = get_token();

            //完全匹配
            $map['keyword_type'] = 0;
            $map['keyword'] = array('like','% '.$keyword.' %');

            $result = M('keyword')->where($map)->order('sort')->limit(1)->select();
            p($result);
            if($result){
                echo 完全;
                //return $result[0];
            }else{
                //模糊匹配
                $map['keyword_type'] = 1;
                $map['keyword'] = array('like',"%".$keyword."%");
                $result = M('keyword')->where($map)->order('sort')->limit(1)->select();
                p($result);echo 模糊;
            }
            echo "************************************";
        }
    }
*/
}

?>