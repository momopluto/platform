<?php
namespace Client\Controller;
use Think\Controller;
class RestaurantController extends ClientController {

	public function lists(){
        $this->show('餐厅列表');
    }

    public function info(){
        $this->show('餐厅信息');
    }

}

?>