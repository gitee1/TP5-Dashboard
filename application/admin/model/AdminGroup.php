<?php
namespace app\admin\model;

use think\Model;

/**
 * 后台用户组模型
 */
class AdminGroup extends Model{

	protected $table="demo_auth_group";

	/**
	 * 添加用户组
	 * @param  array  $data 用户组信息
	 * @return false-添加失败，true-添加成功
	 */
	public function addGroup($data){
		$res=db('auth_group')->insert($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 更新用户组数据
	 * @param  integer  $id   用户组id
	 * @param  array    $data 更新的数据
	 * @return false-更新失败，true-更新成功
	 */
	public function updateGroup($id,$data){
		$res=db('auth_group')->where('id','in',$id)->update($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 删除用户组
	 * @param  array  $id  用户组id
	 * @return false-删除失败，true-删除成功
	 */
	public function delGroup($id){
		$res=db('auth_group')->where('id','in',$id)->delete();
		if($res){
			return true;
		}else{
			return false;
		}
		
		
	}

	/**
	 * 获取用户组成员
	 * @param  integer  $id  用户组id
	 * @return array-用户组成员数组，false-获取失败
	 */
	public function getAdmin($id){
		$res=db('auth_group_access')->where('group_id',$id)->select();
		if($res){
			return $res;
		}else{
			return false;
		}
	}

	/**
	 * 更新用户组成员
	 * @param  integer $group_id 用户组id
	 * @param  array   $uid      成员id数组
	 * @return false-更新失败，更新成功
	 */
	public function updateGroupAccess($group_id,$uid){
		db('auth_group_access')->where('group_id',$group_id)->delete();
		foreach($uid as $uk => $uv){
			$res=db('auth_group_access')->insert(['uid'=>$uv,'group_id'=>$group_id]);
			if(!$res){
				return false;
			}
		}
		return true;
	}
	

}