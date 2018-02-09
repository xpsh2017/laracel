<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action {
    /**
     * 首页
     */
   public function index(){
       $vip=M('access')->where(array('pid'=>1))->getField('level');
       var_dump($vip);die;
   }

}