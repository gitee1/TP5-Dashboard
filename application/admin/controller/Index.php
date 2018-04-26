<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use think\Image;
/**
 * 总控制台
 */
class Index extends Base{
	/**
	 * 总控制台页
	 * @return [type] [description]
	 */
	public function index(){

		return $this->fetch();
	}


}