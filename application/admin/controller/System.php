<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\AdminMenu;
use app\admin\model\AdminAdmin;
use app\admin\model\AdminGroup;
use app\admin\model\AdminRule;

/**
 * 系统管理
 */
class System extends Base{

/************************管理员页*********************************/
	/**
	 * 管理员列表
	 * @return [type] [description]
	 */
	public function admin(){
		$pageSize=6;
		$content=AdminAdmin::paginate($pageSize);
		$page=$content->render();

		$this->assign('content',$content);
		$this->assign('page',$page);

		return $this->fetch();
	}

	/**
	 * 添加管理员
	 */
	public function addAdmin(){
		if(request()->isPost()){
			$name    =input('post.name','','htmlspecialchars');
			$password=input('post.password','','htmlspecialchars');
			$repass  =input('post.repass','','htmlspecialchars');
			$nowpass =input('post.nowpass','','htmlspecialchars');

			$admin=new AdminAdmin;
			$checkName=$admin->check('name',$name);
			$checkNowPass=$admin->check('pass',md5($nowpass));
			if($checkName===true){
				return json(['code'=>0,'msg'=>'管理员名称被占用']);
			}
			if($checkNowPass===false){
				return json(['code'=>0,'msg'=>'当前管理员密码错误']);
			}
			if($password!=$repass){
				return json(['code'=>0,'msg'=>'两次密码输入不一致']);
			}
			$res=$admin->addAdmin(['name'=>$name,'password'=>md5($password)]);
			if($res===true){
				return json(['code'=>1,'msg'=>'添加成功']);
			}else{
				return json(['code'=>0,'msg'=>'添加失败']);
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
	}

	public function updateAdmin(){ 
		if(request()->isPost()){
			$admin=new AdminAdmin;

			$id     =input('post.id','','htmlspecialchars');
			$pass   =input('post.newpass','','htmlspecialchars');
			$repass =input('post.newrepass','','htmlspecialchars');
			$nowpass=input('post.newnowpass','','htmlspecialchars');

			$checkNowPass=$admin->check('pass',md5($nowpass));
			if($checkNowPass===false){
				return json(['code'=>0,'msg'=>'当前管理员密码错误']);
			}
			if($pass!=$repass){
				return json(['code'=>0,'msg'=>'两次密码输入不一致']);
			}
			
			$res=$admin->updateAdmin($id,['password'=>md5($pass)]);
			if($res===true){
				return json(['code'=>1,'msg'=>'密码修改成功。请牢记新密码']);
			}else{
				return json(['code'=>0,'msg'=>'密码修改失败']);
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
	}

	public function statusAdmin(){
		if(request()->isPost()){
			$id=input('post.id','','intval');
			if($id==1){
				return json(['code'=>0,'msg'=>'不能更改系统管理员状态']);
			}else{
				$status=input('post.status','','intval');
				if($status==1 || $status==0){
					$admin=new AdminAdmin;
					$res=$admin->updateAdmin($id,['status'=>$status]);
					if($res===true){
						return json(['code'=>1,'msg'=>'操作成功']);
					}else{
						return json(['code'=>0,'msg'=>'操作失败']);
					}
				}else{
					return json(['code'=>0,'msg'=>'参数错误']);
				}
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
	}

	public function delAdmin(){
		if(request()->isPost()){
			$id=input('post.id','','intval');
			if($id==1){
				return json(['code'=>0,'msg'=>'不能删除系统管理员']);
			}else{
				$admin=new AdminAdmin;
				$res=$admin->delAdmin($id);
				if($res===true){
					return json(['code'=>1,'msg'=>'删除成功']);
				}else{
					return json(['code'=>0,'msg'=>'删除失败']);
				}
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
	}

/************************用户组页*********************************/
	/**
	 * 用户组列表
	 * @return [type] [description]
	 */
	public function group(){
		$pageSize=9;
		$res=AdminGroup::paginate($pageSize);
		$page=$res->render();

		$this->assign('page',$page);
		$this->assign('content',$res);
		return $this->fetch();
		
	}

	/**
	 * 添加用户组
	 * @return [type] [description]
	 */
	public function addGroup(){
		if(request()->isPost()){
			$auth=new AdminGroup;
			$res=$auth->addGroup([
				'title'      =>input('post.title','','htmlspecialchars'),
				'description'=>input('post.description','','htmlspecialchars'),
				'rules'      =>''
			]);
			if($res===true){
				return json(['code'=>1,'msg'=>'添加成功']);
			}else{
				return json(['code'=>0,'msg'=>'添加失败']);
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
	}

	/**
	 * 更改用户组状态
	 * @return [type] [description]
	 */
	public function statusGroup(){
		$id=explode(',',input('get.id'));
		$status=input('get.status','','intval');	
		$data=['status'=>$status];
		$auth=new AdminGroup; 
		$res=$auth->updateGroup($id,$data);

		if($res===true){
			return $this->success('操作成功');
		}else{
			return $this->error('操作失败');
		}
	}

	/**
	 * 删除用户组
	 * @return [type] [description]
	 */
	public function delGroup(){
		$id=explode(',',input('get.id'));
		$auth=new AdminGroup;
		$res=$auth->delGroup($id);
		if($res===true){
			return $this->success('操作成功');
		}else{
			return $this->error('操作失败');
		}
	}

/*****************用户组权限分配/用户分配*************************/
	/**
	 * 权限分配页。两个要素：1、列出所有权限。2、勾选出当前所查看用户组的权限
	 * @return [type] [description]
	 */
	public function giveAuth(){
		$content=[];
		$id       =input('get.id','','intval');
		$groupname=input('get.name','','htmlspecialchars');
		$auth=new AdminRule;
		$res=$auth->getRules($id);

		foreach($res as $rk => $rv){
			$content[$rk]=$rv['name'];//返回一个由rules中name组成的一维数组。
		}
		$rule=new AdminRule;
		//根据导航列表列出所有权限信息
		$allrules=$rule->rulesList();

		$this->assign('allrules',$allrules);
		//列出所有的导航，然后如果该导航url在一维数组res中，则被选中，用in_array()
		$this->assign('gid',$id);
		$this->assign('content',$content);	
		$this->assign('groupname',$groupname);

		return $this->fetch();
	}

	/**
	 * 修改用户组的权限
	 */
	public function updateAuth(){
		$id =input('post.id','','intval');
		$ids=input('post.ids','','htmlspecialchars');
		$group=new AdminGroup;
		$res=$group->updategroup($id,['rules'=>$ids]);
		if($res===true){
			return json(['code'=>1,'msg'=>'操作成功']);
		}else{
			return json(['code'=>0,'msg'=>'操作失败']);
		}
	}

	/**
	 * 用户组成员管理。两个要素，1、有哪些管理员，2、当前浏览的用户组有哪些管理员
	 * @return [type] [description]
	 */
	public function giveAdmin(){
		$content=[];
		$id       =input('get.id','','intval');
		$groupname=input('get.name','','htmlspecialchars');
		$admin=AdminAdmin::select();
		$group=new AdminGroup;
		$groupadmin=$group->getAdmin($id);
		if($groupadmin!==false){
			foreach($groupadmin as $gk => $gv){
				$content[$gk]=$gv['uid'];
			}
		}else{
			$content=[0];
		}
		
		$this->assign('gid',$id);
		$this->assign('admin',$admin);
		$this->assign('groupadmin',$content);
		$this->assign('groupname',$groupname);

		return $this->fetch();
	}
	
	/**
	 * 更新用户组成员
	 * @return [type] [description]
	 */
	public function updateGroupAccess(){
		$id=input('post.id','','intval');
		$ids=explode(',',input('post.ids'));
		$group=new AdminGroup;
		$res=$group->updateGroupAccess($id,$ids);
		if($res===true){
			return json(['code'=>1,'msg'=>'操作成功']);
		}else{
			return json(['code'=>0,'msg'=>'操作失败']);
		}
	}

/************************菜单页***********************************/
	/**
	 * 菜单列表
	 * @return [type] [description]
	 */
	public function menu(){
		$menu=new AdminMenu;
		$res=$menu->showList();
		$parents=AdminMenu::where('pid','0')->select();

		$this->assign('parents',$parents);
		$this->assign('content',$res);

		return $this->fetch();
	}

	/**
	 * 根据id获取菜单信息
	 * @return [type] [description]
	 */
	public function menuInfo(){
		if(request()->isPost()){
			$id=input('post.id','','intval');
			$res=AdminMenu::where('id',$id)->find();
			if($res){
				return json(['code'=>1,'msg'=>$res]);
			}else{
				return json(['code'=>0,'msg'=>'未查到相关信息']);
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
		
	}

	/**
	 * 更新菜单信息
	 * @return [type] [description]
	 */
	public function editMenu(){
		if(request()->isPost()){
			$id=input('post.id','','intval');
			$data=[
				'pid'    => input('post.pid','','intval'),
				'title'  => input('post.title','','htmlspecialchars'),
				'url'    => input('post.url','','htmlspecialchars'),
				'icon'   => input('post.icon','','htmlspecialchars'),
				'sort'   => input('post.sort','','intval'),
				'status' => input('post.status','','intval')
			];
			$menu=new AdminMenu;
			if($id==0){
				$res=$menu->addMenu($data);
			}else{
				$res=$menu->updateMenu($id,$data);
			}
			
			if($res===true){
				return json(['code'=>1,'msg'=>'操作成功']);
			}else{
				return json(['code'=>0,'msg'=>'操作失败']);
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
	}

	/**
	 * 删除节点(菜单)
	 * @return [type] [description]
	 */
	public function delNode(){
		$id=input('post.id','','intval');
		$menu=new AdminMenu;
		$res=$menu->delNode($id);
		if($res===1){
			return json(['code'=>1,'msg'=>'删除成功']);
		}elseif($res===0){
			return json(['code'=>0,'msg'=>'删除失败']);
		}elseif($res===-1){
			return json(['code'=>1,'msg'=>'当前菜单有子菜单，请先删除子菜单后再操作']);
		}
	}

/************************权限页***********************************/
	/**
	 * 权限列表
	 * @return [type] [description]
	 */
	public function rules(){
		$rules=new AdminRule;
		$res=$rules->rulesList();
		$fatherRule=AdminRule::where('pid',0)->select();

		$this->assign('father',$fatherRule);
		$this->assign('res',$res);
		
		return $this->fetch();
	}

	/**
	 * 根据id获取权限信息
	 * @return [type] [description]
	 */
	public function getRule(){
		if(request()->isPost()){
			$id=input('post.id','','intval');
			$res=AdminRule::where('id',$id)->find();
			if($res){
				return json(['code'=>1,'msg'=>$res]);
			}else{
				return json(['code'=>0,'msg'=>'获取信息失败']);
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
	}

	/**
	 * 更改或者新增权限
	 * @return [type] [description]
	 */
	public function updateRules(){
		if(request()->isPost()){
			$id=input('post.id','','intval');
			$pid=input('post.pid','','intval');
			$title=input('post.title','','htmlspecialchars');
			$name=input('post.name','','htmlspecialchars');
			$data=[
				'pid'=>$pid,
				'title'=>$title,
				'name' =>$name
			];
			$rule=new AdminRule;
			if($id==0){
				$res=$rule->addRules($data);
			}else{
				$res=$rule->updateRules($id,$data);
			}
			
			if($res===true){
				return json(['code'=>1,'msg'=>'操作成功']);
			}else{
				return json(['code'=>0,'msg'=>'操作失败']);
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
	}

	/**
	 * 删除权限规则
	 * @return [type] [description]
	 */
	public function delRule(){
		if(request()->isPost()){
			$id=input('post.id','','intval');
			$rule=new AdminRule;
			$res=$rule->delRules($id);
			if($res===1){
				return json(['code'=>1,'msg'=>'删除成功']);
			}elseif($res===-1){
				return json(['code'=>0,'msg'=>'删除前请将下属权限全部删除']);
			}elseif($res===-2){
				return json(['code'=>0,'msg'=>'不能删除全部权限']);
			}else{
				return json(['code'=>0,'msg'=>'删除失败']);
			}
		}else{
			return json(['code'=>0,'msg'=>'非法请求']);
		}
	}

	
}