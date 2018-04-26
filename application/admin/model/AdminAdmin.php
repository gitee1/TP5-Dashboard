<?php
namespace app\admin\model;

use think\Model;

/**
 * 后台管理员模型
 */
class AdminAdmin extends Model{

	protected $table='demo_admin';

	/**
	 * 添加管理员
	 * @param array  $data 管理员信息
	 * @return false-操作失败，true-操作成功
	 */
	public function addAdmin($data){
		$res=db('admin')->insert($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 添加管理员时校验管理员名称是否可用，校验当前管理员密码
	 * @param  string $type 'name'-校验名称，'pass'-校验密码
	 * @param  string $data 'name'/'pass'的值
	 * @return $type='name'时，false-表示名称可用,true-表示名称不可用
	 *         $type='pass'时，false-表示验证失败,true-表示验证成功
	 */
	public function check($type,$data){
		switch($type){
			case "name":
				$res=db('admin')->where('name',$data)->value('id');
				break;
			case "pass":
				$res=db('admin')->where(['id'=>session('aid'),'password'=>$data])->value('id');
				break;
		}
		
		if($res){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 更新管理员信息
	 * @param  integer $id    管理员id
	 * @param  array   $data  更新的数组
	 * @return true-更新成功，false-更新失败
	 */
	public function updateAdmin($id,$data){
		$res=db('admin')->where('id',$id)->update($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 删除管理员
	 * @param  integer  $id  管理员id
	 * @return true-删除成功，false-删除失败
	 */
	public function delAdmin($id){
		$res=db('admin')->where('id',$id)->delete();
		if($res){
			return true;
		}else{
			return false;
		}
	}

}