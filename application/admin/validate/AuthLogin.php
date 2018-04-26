<?php
namespace app\admin\validate;

use think\Validate;

class AuthLogin extends Validate{
	protected $rule=[
		'name|管理员名称' => 'require',
		'password|管理员密码' => 'require',
		'captcha|验证码'  => 'require',
	];
}