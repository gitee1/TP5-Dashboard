<?php
namespace app\admin\model;

use think\Model;

/**
 * 后台菜单模型
 */
class AdminMenu extends Model{
	
	protected $table="demo_menu";

	/**
	 * 获取父菜单
	 * @return  array  父菜单信息
	 */
	public function getParent(){
		//加查询缓存
		$res=db('menu')
		     ->cache('p_node',0,'navNode')
		     ->where(['pid'=>'0','status'=>'1'])
		     ->order('sort','desc')
		     ->select();
		return $res;
	}

	/**
	 * 获取父菜单及其对应的子菜单
	 * 用于后台模板获取菜单
	 * @return array 菜单数组
	 */
	public function getNodes(){
		$parents=$this->getParent();
		foreach($parents as $pk => $pv){
			$nodes[$pk]['id']    =$pv['id'];
			$nodes[$pk]['sort']  =$pv['sort'];
			$nodes[$pk]['status']=$pv['status'];
			$nodes[$pk]['title'] =$pv['title'];
			$nodes[$pk]['icon']  =$pv['icon'];
			$nodes[$pk]['url']   =$pv['url'];
			//加查询缓存
			$nodes[$pk]['child']=db('menu')
			                     ->cache('c_node_'.$pk,0,'navNode')
								 ->where(['pid'=>$pv['id'],'status'=>'1'])
								 ->order('sort','desc')
								 ->select();
		} 
		return $nodes;
	}

	/**
	 * 用于后台系统管理/菜单列表，拉取所有菜单列表 
	 * @return  array  包含所有菜单的数组
	 */
	public function showList(){
		$parents=db('menu')->where(['pid'=>'0'])->order('sort','desc')->select();
		foreach($parents as $pk => $pv){
			$nodes[$pk]['id']    =$pv['id'];
			$nodes[$pk]['sort']  =$pv['sort'];
			$nodes[$pk]['status']=$pv['status'];
			$nodes[$pk]['title'] =$pv['title'];
			$nodes[$pk]['icon']  =$pv['icon'];
			$nodes[$pk]['url']   =$pv['url'];
			$nodes[$pk]['child'] =db('menu')
			                     ->where(['pid'=>$pv['id']])
			                     ->order('sort','desc')
			                     ->select();
		}
		return $nodes;
	}

	/**
	 * 添加菜单
	 * @param   array   $data   菜单信息
	 * @return  false-添加失败，true-添加成功
	 */
	public function addMenu($data){
		$res=db('menu')->insert($data);

		if($res){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 更新菜单
	 * @param  integer $id   菜单id
	 * @param  array   $data 菜单信息
	 * @return false-更新失败，true-更新成功
	 */
	public function updateMenu($id,$data){
		$res=db('menu')->where('id',$id)->update($data);

		if($res){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 删除节点
	 * 规则：子菜单随意删除。父菜单有子菜单时不可删除，无子节点可删除。
	 * @param  integer $id 菜单id
	 * @return -1-有子菜单，不可删除，0-删除失败，1-删除成功 
	 */
	public function delNode($id){
		//直接看有没有菜单的pid为当前菜单id，有则说明有子节点。不可删除
		$nodes=db('menu')->where('pid',$id)->find();
		
		if($nodes){	//说明有子节点，不可删除
			return -1;
		}else{
			$res=db('menu')->where('id',$id)->delete();
			if($res){
				return 1;
			}else{
				return 0;
			}
		}
	}

}