<?php
namespace app\admin\model;

use think\Model;

/**
 * 后台管理员登录模型
 */
class AdminLogin extends Model{

	/**
	 * 登录时，检查输入的管理员信息
	 * @param  array $data 	登录信息数组
	 * @return -1-账户被禁用  0-帐号密码错误  1-登录成功，返回管理员id
	 */
	public function checkLogin($data){
		$res=db('admin')->where($data)->find();

		if($res){
			if($res['status']==1){
				return array('id'=>$res['id'],'name'=>$res['name']);
			}else{
				return -1;
			}	
		}else{
			return 0;
		}
	}

	/**
	 * 管理员登录成功，将信息写入session
	 * @param  array  $admin  存储管理员信息的数组
	 * @param  array  $data   更新的数据
	 * @return false-写入数据失败，true-写入数据成功
	 */
	public function updateLogin($admin,$data){
		$res=db('admin')->where('id',$admin['id'])->update($data);
		
		if($res){
			session('aid',$admin['id']);
			session('adminname',$admin['name']);
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 管理员退出登录
	 * @return integer false-无登录信息，true-退出成功
	 */
	public function logout(){
		if(session('aid')){
			session('aid',null);
			session('adminname',null);
			return true;
		}else{
			return false;
		}
	}
}