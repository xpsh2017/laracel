<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->redirect('SiteDaily/index');
        $this->display();
    }

    public function gc()
    {
        $type = I('get.type', 'c');
        $module = 'Home';
        $name = I('get.name');
        if($type == 'c')
        {
            $f = \Think\Build::buildController($module, $name);
        }
        elseif($type == 'm'){
            $f = \Think\Build::buildModel($module, $name);
        }
        var_dump($f);
    }
}