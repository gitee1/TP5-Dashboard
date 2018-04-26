<?php
namespace app\admin\model;

use think\Model;
use think\facade\Cache;

/**
 * 后台权限模型
 */
class AdminRule extends Model{
	
	protected $table="demo_auth_rule";

	/**
	 * 根据用户组id获取用户组的权限
	 * @param  integer  $id  权限id
	 * @return array-权限数组
	 */
	public function getRules($id){
		$rules=db('auth_group')->where('id',$id)->value('rules');
		$rule=explode(',',$rules);
		$res=db('auth_rule')->field('name')->where('id','in',$rule)->select();
		
		return $res;	
	}

	
	/**
	 * 获取所有权限,后台权限列表页输出
	 * @return array  权限数组
	 */
	public function rulesList(){
		//添加缓存，缓存打上标签-rulesList,执行添加、更新、删除操作时删除缓存
		$content=db('auth_rule')->cache('auth_rule_parent',7200,'rulesList')->where('pid','0')->select();
		foreach($content as $ck => $cv){
			$res[$ck]['pid']    = $cv['pid'];
			$res[$ck]['id']     = $cv['id'];
			$res[$ck]['title']  = $cv['title'];
			$res[$ck]['url']    = $cv['name'];
			$res[$ck]['child']  = db('auth_rule')->where('pid',$cv['id'])
			                                     ->cache('auth_rule_child_'.$cv['id'],7200,'rulesList')
			                                     ->select();
		}
		return $res;
	}

	/**
	 * 添加权限
	 * @param   array   $data  权限信息
	 * @return  false-添加失败，true-添加成功
	 */
	public function addRules($data){
		$res=db('auth_rule')->insert($data);
		if($res){
			//添加成功，删除缓存
			Cache::clear('rulesList');
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 更新权限信息
	 * @param  integer $id   权限id
	 * @param  array  $data  权限信息
	 * @return false-更新失败，true-更新成功
	 */
	public function updateRules($id,$data){
		$res=db('auth_rule')->where('id',$id)->update($data);
		if($res){
			//更新成功，删除缓存
			Cache::clear('rulesList');
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 删除权限
	 * @param  integer  $id  权限id
	 * @return -1-有子权限、须先将子权限删除后在操作，0-删除失败
	 */
	public function delRules($id){
		$pid=db('auth_rule')->where('pid',$id)->find();
		if(!$pid){
			$count=db('auth_rule')->count();
			if($count==1){
				return -2;
			}else{
				$res=db('auth_rule')->where('id',$id)->delete();
				if($res){
					//删除成功，删除缓存
					Cache::clear('rulesList');
					return 1;
				}else{
					return 0;
				}
			}
		}else{
			return -1;
		}
	}

}