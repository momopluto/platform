<?php
namespace Client\Controller;
use Think\Controller;
class IndexController extends Controller {

    public function index(){
        // echo "Clinet/Index/index.html";
        redirect(U('Client/Restaurant/lists'));
    }

}