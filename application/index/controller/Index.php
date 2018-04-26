<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller{
    public function index()
    {
    	$a=1;
    	$this->assign('a',$a);
        return $this->fetch();
    }

}
