<?php
namespace app\admin\controller;

use think\Controller;
use think\View;
use Auth\Auth;
use app\admin\model\AdminMenu;

class Base extends Controller{

	protected function _initialize(){
		//检测是否登录，没登录则跳转到登录页面
		if(!session('aid')){
			return $this->redirect('login/login');
		}

		$this->assign('hi','Hello!');

		//处理左侧导航栏
		$menu=$this->getMenu();
		$this->assign('menu',$menu);

		//权限检测，不检测id为1的管理员，这里是系统管理员system
		if(session('aid')!=1){
 			$hasAuth=$this->hasAuth();
	 		if($hasAuth!==false){
	 			return $this->error('没有操作权限','index/index');
	 		}
		}
		//拼接出模块/控制器
		$parent_url=strtolower(Request()->module().'/'.Request()->controller());
		//拼接出模块/控制器/操作
		$child_url=strtolower(Request()->module().'/'.Request()->controller().'/'.Request()->action());

		$this->assign('parent_url',$parent_url);
		$this->assign('child_url',$child_url);
	}

	/**
	 * 根据权限动态拉取显示菜单
	 * @return [type] [description]
	 */
	public function getMenu(){
		$a=[];
		$menu=new AdminMenu;
		$res=$menu->getNodes();
		/**
		 * 先判断有没有子菜单的权限，没有就删除。
		 * 当父菜单没有子菜单时，通过有无child键判断，删除父菜单。
		 * 然后判断当'child'=>array(0)时，判断父菜单有无权限，无就删除。
		**/ 
		//系统管理员不做处理
		if(session('aid')!=1){
			$auth=new Auth;
			foreach($res as $rk => $rv){
				foreach($rv['child'] as $ck => $cv){
					if(!$auth->check($cv['url'],session('aid'))){
						//删除无权限的子菜单
						unset($res[$rk]['child'][$ck]);
					}
				}	
				//删除无子菜单的父菜单
				if(!isset($res[$rk]['child'])){	
					unset($res[$rk]);
				}
				//删除'child'=>array(0)且无权限的父菜单
				if(empty($res[$rk]['child']) && !$auth->check($rv['url'],session('aid'))){	
					unset($res[$rk]);
				}
			}
		}
		return $res;
	}

	/**
	 * 判断有无权限
	 * @return boolean [description]
	 */
	public function hasAuth(){
		//获取模块
		$module=Request()->module();
		//获取模块/控制器
		$controller=Request()->module().'/'.Request()->controller();
		//获取模块/控制器/操作
 		$act=strtolower(Request()->module().'/'.Request()->controller().'/'.Request()->action());

 		$auth=new Auth;
 		//权限判断
 		if(!$auth->check($act,session('aid')) && !$auth->check($module,session('aid')) && !$auth->check($controller,session('aid'))){
 			return true;
 		}else{
 			return false;
 		}
	}

}