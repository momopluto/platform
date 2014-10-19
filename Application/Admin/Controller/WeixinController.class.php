<?php

namespace Admin\Controller;
// use Think\Log;
/**
 * 微信交互控制器
 * 主要获取和反馈微信平台的数据
 */
class WeixinController extends AdminController {

    /* 空操作，用于输出404页面 */
    public function _empty(){
        // redirect("http://www.qq.com/404");
    }

    //初始化操作
    function _initialize() {

    }

    var $token;
    private $data = array();
    public function index() {
        //删除微信传递的token干扰
        unset($_REQUEST['token']);

        $weixin = D ( 'Weixin' );
        // 获取数据
        $data = $weixin->getData ();

        $this->data =$data;
        if (! empty ( $data ['ToUserName'] )) {
            get_token ( $data ['ToUserName'] );
        }
        if (! empty ( $data ['FromUserName'] )) {
            session ( 'openid', $data ['FromUserName'] );
        }

        $this->token = $data ['ToUserName'];

        // 记录日志
        addWeixinLog ( $data, $GLOBALS ['HTTP_RAW_POST_DATA'] );

        // 回复数据
        $this->reply ( $data, $weixin );

        // 结束程序。防止oneThink框架的调试信息输出
        exit ();
    }
    private function reply($data, $weixin) {

        /**
         * 通过微信事件来定位处理的插件
         * event可能的值：
         * subscribe : 关注公众号
         * unsubscribe : 取消关注公众号
         * scan : 扫描带参数二维码事件
         * location : 上报地理位置事件
         * click : 自定义菜单事件
         */
        if ($data ['MsgType'] == 'event') {
            $event = strtolower ( $data ['Event'] );
            if($event == 'view' && ! empty ( $data ['EventKey'] )){//自定义菜单事件，跳转url
                return;
            }elseif ($event == 'click' && ! empty ( $data ['EventKey'] )) {//自定义菜单事件，关键字
                $key = $data ['EventKey'];
                $weixin->menu_handle($key);
                return;
            }elseif($event == 'subscribe'){//关注公众号

                // 粉丝信息，数据库操作
                if(M('follow')->where(array('token' => $this->token, 'openid' => $data['FromUserName']))->find()){//数据库已有该粉丝信息
                    $update_info = array(
                        'subscribe_time' => $data['CreateTime'],
                        'status' => 1
                    );
                    M('follow')->where(array('token' => $this->token, 'openid' => $data['FromUserName']))->setField($update_info);
                }else{//全新的粉丝
                    //将粉丝信息添加入数据库cms_follow表
                    $one_follow = array(
                        'token' => $this->token,
                        'openid' => $data['FromUserName'],
                        'subscribe_time' => $data['CreateTime'],
                    );
                    M('follow')->add($one_follow);
                }

                // 欢迎语
                $weixin->subscribe();

            }elseif($event == 'unsubscribe'){//取消关注公众号

                //将粉丝关注标志status设为0，表示取消关注
                M('follow')->where(array('token' => $this->token, 'openid' => $data['FromUserName']))->setField('status',0);
                //将粉丝信息从数据库cms_follow表删除
                //M('follow')->where(array('token' => $this->token, 'openid' => $data['FromUserName']))->delete();

                // return;
            }elseif($event == 'scan'){

            }elseif($event == 'location'){

            }  else {
                return false;
            }
        }

        if($data ['Content'] != null ){
            $key = $data ['Content'];
            //关键字回复
            $weixin->keyword_handle($key);
        }


//        if (! isset ( $addons [$key] )) {
//            $like ['keyword'] = $key;
//            $like ['keyword_type'] = 0;
//            $keywordArr = M ( 'keyword' )->where ( $like )->order ( 'id desc' )->find ();
//
//            if(!empty($keywordArr ['addon'])){
//                $addons [$key] = $keywordArr ['addon'];
//                $this->request_count($keywordArr);
//            }
//        }
//        // 通过模糊关键词来定位处理的插件
//        if (! isset ( $addons [$key] )) {
//            unset ( $like ['keyword'] );
//            $like ['keyword_type'] = array (
//                'gt',
//                0
//            );
//            $list = M ( 'keyword' )->where ( $like )->order ( 'keyword_length desc, id desc' )->select ();
//
//            foreach ( $list as $keywordInfo ) {
//                $this->_contain_keyword ( $keywordInfo, $key, $addons, $keywordArr );
//            }
//        }
//
//
//        //通过通配符，查找默认处理方式
//        //by 肥仔聪要淡定 2014.6.8
//        if (! isset ( $addons [$key] )) {
//            unset ( $like ['keyword_type'] );
//            $like ['keyword'] ='*';
//            $keywordArr = M ( 'keyword' )->where ( $like )->order ( 'id desc' )->find ();
//
//            if(!empty($keywordArr ['addon'])){
//                $addons [$key] = $keywordArr ['addon'];
//                $this->request_count($keywordArr);
//            }
//        }
//
//        // 最终也无法定位到插件，终止操作
//        if (! isset ( $addons [$key] ) || ! file_exists ( ONETHINK_ADDON_PATH . $addons [$key] . '/Model/WeixinAddonModel.class.php' )) {
//            return false;
//        }
//
//        // 加载相应的插件来处理并反馈信息
//        require_once ONETHINK_ADDON_PATH . $addons [$key] . '/Model/WeixinAddonModel.class.php';
//        $model = D ( 'Addons://' . $addons [$key] . '/WeixinAddon' );
//        $model->reply ( $data, $keywordArr );

        //运营统计
        //tongji ( $addons [$key] );
    }


    // 保存关键词的请求数
    private function request_count($keywordArr){
        $map['id'] = $keywordArr['id'];
        M ( 'keyword' )->where ( $map )->setInc( 'request_count' );
    }

}
?>