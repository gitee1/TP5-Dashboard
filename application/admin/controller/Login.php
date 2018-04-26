<?php
namespace app\admin\controller;

use think\Cache;
use think\Controller;
use think\captcha\Captcha;
use app\admin\model\AdminLogin;

/**
 * 管理员登录
 */
class Login extends Controller{
	
	/**
	 * 管理员登录时验证
	 * @return [type] [description]
	 */
	public function login(){
		if(request()->isPost()){	//判断表单空否，验证验证码，判断信息是否正确（，更新管理员状态）
			if(!input('post.autoLogin')){
				$result = $this->validate(
	            [
	                'name'     => input('post.name'),
	                'password' => input('post.name'),
	                'captcha'  => input('post.captcha'),
	            ],
	            'app\admin\validate\AuthLogin');
		        if (true !== $result) {
		            // 验证失败 输出错误信息
		            return json(['code'=>0,'msg'=>'请正确填写信息']);
		        }

				$value=input('post.captcha');
				$captcha = new Captcha();
		
				if(!$captcha->check($value)){
					return json(['code'=>0,'msg'=>'验证码填写错误']);
				}
			}
			
			$data=[
				'last_time' => time(),
				'last_ip' => get_client_ip()
			];
			$login=new AdminLogin;
			$res=$login->checkLogin(['name'=>input('post.name'),'password'=>input('post.password')]);	//检查管理员信息

			if($res===-1){	
				return json(['code'=>0,'msg'=>'该账户被禁用']);
			}elseif($res===0){
				return json(['code'=>0,'msg'=>'用户名或密码不正确']);
			}else{
				$rr=$login->updateLogin($res,$data);
				if($rr===true){
					$send=['name'=>input('post.name'),'password'=>input('post.password')];
					return json(['code'=>1,'msg'=>$send]);
				}else{
					return json(['code'=>0,'msg'=>'登录失败']);
				}	
			}
		}else{
			return $this->fetch();
		}
		
	}

	/**
	 * 后台登录页生成验证码
	 * @return [type] [description]
	 */
	public function verify(){
		$captcha = new Captcha();
        return $captcha->entry();    
	}

	/**
	 * 管理员退出登录
	 * @return [type] [description]
	 */
	public function logout(){
		$login=new AdminLogin;
		$res=$login->logout();
		if($res===true){
			return $this->redirect('login');
		}else{
			return $this->error('未登录','login');
		}
	}

	/**
	 * 清除缓存
	 * @return [type] [description]
	 */
	public function cacheClear(){
		//清除菜单列表的缓存
		Cache::clear('navNode');
		return $this->redirect('index/index');
	}

}