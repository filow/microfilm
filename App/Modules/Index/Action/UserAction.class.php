<?php
class UserAction extends CommonAction {
	public function login(){
		// 获取用户名和密码
		$uid=I('username');
		$password=I('password');
		if(!$uid || !$password)
			$this->message('请完整地填写用户名和密码');

		// 查询用户信息
		$user=M('user');
		$result=$user->where(array('uid'=>$uid))->find();
		if($result['forbidden']==1) $this->message('您的账户已经被管理员锁定！');
		
		// 判断是否符合要求
		if(passwordHash($uid,$password)==$result['password']){
			// 设置用户信息
			sess('acc',$result);
			// 是否首次登录
			if($result['is_first_login']!=0)
				$user->where(array('id'=>$result['id']))->setField('is_first_login',0);
			// 跳转
			$this->redirect('UserCenter/index');
		}else{
			$this->message('您输入的用户名密码有误，请重新尝试');
		}
	}
	/**
	 * 用于在重试窗口显示错误信息的方法
	 * @param  [String] $msg [要显示的信息]
	 */
	private function message($msg){
		XS('LOGIN_FAIL_MESSAGE',$msg);
		$this->redirect('retry');
	}
	// 登录失败，重试
	public function retry(){
		$this->assign('message',XS('LOGIN_FAIL_MESSAGE'));
		XS('LOGIN_FAIL_MESSAGE',null);
		$this->display();
	}
	// 忘记密码
	public function forgot(){
		$this->display();
	}
	// 检查uid是否可用，ajax方法
	public function checkUid(){
		$acc=sess('acc');
		$uid=I('uid');
		if(!$uid) {echo "-1";die;}
		$user=M('user');
		if($acc['id']){
			$result=$user->where(array('uid'=>$uid,'id'=>array('neq',$acc['id'])))->count();
		}else{
			$result=$user->where(array('uid'=>$uid))->count();
		}
		
		if($result)
			echo "1";
		else
			echo "0";
	}
	// 显示用户信息的页面
	public function userinfo(){
		$uid=I('uid');
		if(!$uid)
			$this->error('没有这个用户！');
		
		$user=D('User');
		$opus=M('opus');
		// 用户信息
		$data=$user->where(array('uid'=>$uid))->find();
		// 视频信息
		$opus_data=M('opus')->
						where(array('user_id'=>$data['id'],'status'=>0))->
						select();
		$data['opus_count']=count($opus_data);
		// 统计播放总数
		$view_count=0;
		foreach ($opus_data as $key => $value) {
			$view_count+=$value['view_count'];
		}
		// 用户观看第一个视频
		viewOpus($opus_data[0]['id']);
		importP('Attach');
		$opus_data[0]['video']=Attach::getVideo($opus_data[0]['id']);
		$this->assign('opus_first',$opus_data[0]);
		unset($opus_data[0]);

		// 其他信息
		$this->assign('opus',$opus_data);
		$this->assign('user',$data);
		$this->assign('view_count',$view_count);

		$this->display();
	}
	// 退出登录
	public function logout(){
		unset($_SESSION['_INDEX_']['acc']);
		$this->redirect('Index/Index');
	}
	// 注册
	public function reg(){
		$this->display();
	}
	// 注册处理方法
	public function addUser(){
        $user=M('user');
        $data=$user->create();  // 用于删除不必要的字段
        // 检查合法性
        if($data['uid']=="" || strlen($data['uid'])<5 )
        	$this->error('用户ID不能为空！且必须5位以上');
        if($data['nickname']=="")
            $this->error('用户姓名不能为空！');
        if($data['password']=="" || strlen($data['password'])<6 )
            $this->error('密码不能为空！且必须大于6位');
		// 密码单向加密
        $data['password']=passwordHash($data['uid'],$data['password']);
        $id=$user->add($data);
        if(!$id){
        	$this->error('用户添加失败！');
        }
        $this->success('用户添加成功！',U('UserCenter/Index'));
	}
}