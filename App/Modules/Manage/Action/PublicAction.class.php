<?php
class PublicAction extends Action {
	/**
	 * 后台登录首页
	 */
	public function Index(){
		$fail_time=XS('LOGIN_FAIL_TIME');
		$fail_timeout=CF('LOGIN_FAIL_TIMEOUT');

		if(session('uid')){
			$this->success('您已经登录过，现在将跳转回首页',U('Manage/Index/index'));
			die;
		}

		if($fail_time > 0 || XS('LOGIN_FAIL_MESSAGE') || XS('fail_wait')){
			//如果超过指定次数则显示验证码
			if($fail_time >= CF('LOGIN_FAIL_TIME_VERIFY')){
				XS('show_verify',1,$fail_timeout);
			}
			//暂停登录尝试
			if($fail_time >= CF('LOGIN_FAIL_TIME_WAIT') && !XS('fail_wait')){
				XS('fail_wait',time()+$fail_timeout,$fail_timeout);
				XS('LOGIN_FAIL_TIME',null);
				XS('LOGIN_FAIL_TIME',NULL);
			}
			$this->assign('show_info_box',1);
			$this->assign('login_fail_time',$fail_time);
			$this->assign('login_fail_message',XS('LOGIN_FAIL_MESSAGE'));	
			XS('LOGIN_FAIL_MESSAGE',NULL);
		}

		$this->display();
	}
	public function login(){
		//如果强制提交，直接中断程序
		if(XS('fail_wait')){
			$this->error('您已被禁止登录！');
		}
		//检查是否有字段没有填写
		if(!I('uid') || !I('password')){
			XS('LOGIN_FAIL_MESSAGE',"请填写用户名和密码<br />",60);
			$this->RedirecttoIndex();
		}
		if(XS('show_verify')){
			if(I('vcode')){
				importP('VerifyCode');
				if(!VerifyCode::check(I('vcode'))){
					XS('LOGIN_FAIL_MESSAGE',"验证码错误<br />",60);
					$this->RedirecttoIndex();
				}
			}else{
				XS('LOGIN_FAIL_MESSAGE',"请填写验证码<br />",60);
				$this->RedirecttoIndex();
			}
		}
		$admin=new AdminModel(I('uid'),I('password'));
		$result=$admin->checkAccount();

		if($result==-1){     //登录成功，但账户被禁用
			XS('LOGIN_FAIL_MESSAGE',"抱歉，您的管理员账户已被停用<br />",60);
			XS('LOGIN_FAIL_TIME',NULL);
			$this->RedirecttoIndex();
		}else if($result==0){   //登录失败
			XS('LOGIN_FAIL_MESSAGE',"抱歉，您输入的账号密码有误<br />",60);
			XS('LOGIN_FAIL_TIME',XS('LOGIN_FAIL_TIME')+1,CF('LOGIN_FAIL_TIME_SPAN'));
			$this->RedirecttoIndex();
		}else{
			XS('LOGIN_FAIL_TIME',NULL);
			$admin->setAccountData();
			$this->redirect('Manage/Index/Index');
		}
	}
	private function RedirecttoIndex(){
		$this->redirect('Index',array('uid'=>I('uid')));
	}
    public function vcode(){
    	importP('VerifyCode');
    	VerifyCode::$useNoise = true;  // 要更安全的话改成true
		VerifyCode::$useCurve = true;
		VerifyCode::createImage();
    }
    public function test(){
    	session(null);
    }
    public function logout(){
    	session(null);
    	$this->redirect('Index');
    }
}