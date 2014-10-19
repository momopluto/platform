<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 餐厅管理
 *
 */
class ShopmanageController extends HomeController{
	
	protected $model;
	// 初始化操作
	function _initialize() {
        parent::_initialize ();

        $this->model = D('resturant');
	}

    // 设置
    function set(){
    	$rid = 10086 + session('uid');
    	if(IS_POST){
            // p(I('post.'));die;
            $data = I('post.');

        /*======================================上传餐厅logo begin==================================*/
            // p($_FILES);die;
            $logo_url = null;
            if($_FILES['photo']['error'] == 4){
                // $logo_url = null;
            }else{
                $config = array(//图片上传配置
                    'maxSize'    =>    3145728,    
                    'rootPath'   =>    './Application/Uploads',
                    'savePath'   =>    '/rst_logo/',    
                    'saveName'   =>    md5(session(uid)),   //md5加密用户的uid作为logo名
                    'exts'       =>    array('jpg', 'png', 'jpeg'),    
                    'autoSub'    =>    false,   //子目录，关闭    
                    // 'subName'    =>    array('date','Ymd'),
                    'replace'    =>    true,    //允许同名文件覆盖
                );
                $upload = new \Think\Upload($config);// 实例化上传类  
                $info   =   $upload->uploadOne($_FILES['photo']);

                if(!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                }else{// 上传成功
                    $logo_url = DOMAIN_URL."/platform/Application/Uploads/rst_logo/".$info['savename'];
                }
            }
        /*======================================上传餐厅logo end==================================*/
            
            if(is_null($logo_url)){//没有上传图片
                if($temp = $this->model->where("rid = $rid")->find()){//如果原数据已创建，则使用原来的logo_url
                    $logo_url = $temp['logo_url'];
                }
            }
            $data['logo_url'] = $logo_url;

            $data['rid'] = $rid;

    		if($data['rst_is_bookable'] === 'on'){
    			$data['rst_is_bookable'] = 1;
    		}
    		else{
    			$data['rst_is_bookable'] = 0;
    		}

    		// if($data['stime_alwaysOpen'] === 'on'){
    		// 	$data['stime_alwaysOpen'] = 1;
    		// }else{
    		// 	$data['stime_alwaysOpen'] = 0;
    		// }
            $data['logo_url'] = $logo_url;
    		// p($data);die;

    		if(!$this->model->where("rid = $rid")->find()){//未录入数据库
    			// echo "首次";die;
                if(is_null($data['logo_url'])){//新建数据时，未上传照片，则使用默认
                    // $data['logo_url'] = DOMAIN_URL."/platform/Application/Uploads/rst_logo/default_rst_logo.jpg";
                    unset($data['logo_url']);
                }                
                // p($data);die;
				if($this->model->create($data) && $this->model->add($data)){
                    M('allrst', 'admin_')->where("rid = $rid")->setField('rst_name', $data['rst_name']);//更新admin_allrst中的餐厅名
	    			$this->success('餐厅信息创建成功！');
	    		}else{
	    			$this->error($this->model->getError());
	    		}
    		}else{//更新餐厅信息（如果未改动过信息，不能成功保存）
    			// echo "非";die;
    			if($this->model->where("rid = $rid")->save($data)){
                    M('allrst', 'admin_')->where("rid = $rid")->setField('rst_name', $data['rst_name']);//更新admin_allrst中的餐厅名
	    			$this->success('餐厅信息保存成功！');
	    		}else{
	    			$this->error($this->model->getError());
	    		}
    		}
    	}else{
	        if($data = $this->model->where("rid = $rid")->find()){//已有餐厅信息
	        	// p($data);die;
	        }else{
	        	//默认值
	        	$data['rst_agent_fee'] = 0;//外加配送费
	        	$data['rst_is_bookable'] = 1;//是否接收预订
	        	$data['stime_alwaysOpen'] = 0;//24小时营业
	        	$data['warning_tone'] = 0;//提示音
	        }

            if(!isset($data['logo_url'])){
                $data['logo_url'] = DOMAIN_URL."/platform/Application/Uploads/rst_logo/default_rst_logo.jpg";
                // echo $data['logo_url'];die;
            }

    		$this->assign('data', $data);

    		$this->display();
    	}
    }
}

?>