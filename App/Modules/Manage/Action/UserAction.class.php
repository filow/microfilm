<?php
class UserAction extends CommonAction {
    public function index()
    {
    	$user=D('User');
    	$user_data=$user->getUserInfo(null,'');
        
    	$this->assign('user_data',$user_data);
		$this->display();
    }
    public function action_handle(){
    	switch ($_POST['type']) {
    		case 'edit':
    			$this->edit();
    			break;
    		case 'view':
    			$this->view();
    			break;
    		case 'lock':
    			$this->lock();
    			break;
    		case 'delete':
    			$this->delete();
    			break;
            case 'sendmsg':
                A('Passage')->sendmsg($_POST['select']);
                break;
    		default:
    			XS('message',array(0,"操作不正确！"));
    			$this->redirect('index');
    			break;
    	}
    }
    private function view(){
    	$user=D('User');
    	$user_data=array();
    	foreach ($_POST['select'] as $key => $value) {
    		$tmp=$user->getUserInfo($value);
    		$user_data[]=$tmp[0];
    	}
    	$this->assign('user_data',$user_data);
    	$this->display('view');
    }
    private function edit(){
    	$user=D('User');
    	$user_data=array();
    	foreach ($_POST['select'] as $key => $value) {
    		$tmp=$user->getUserInfo($value);
    		$user_data[]=$tmp[0];
    	}
    	$this->assign('user_data',$user_data);
    	$this->display('edit');
    }
    public function edit_handle()
    {
        if(!I('id')){echo "<a class='text-error'>没有传入ID</a>";return;}
        $user=M('user');
        $user_data=$user->where(array('id'=>I('id')))->find();
        $data=$user->create();
        if($data['password']==""){
            unset($data['password']);
        }else{
            $data['password']=passwordHash($user_data['uid'],$data['password']);
        }
        $result=$user->where(array('id'=>$data['id']))->save($data);
        if($result){
            echo "<a class='text-success'>用户信息修改成功</a>";
        }else{
            echo "<a class='text-error'>用户信息修改失败</a>";
        }
    }
    public function avatar_upload_iframe(){
        C('SHOW_PAGE_TRACE',false);  //关闭页面trace
        $id=I('id');
        $user=M('user');
        if($_FILES){
            importP("Attach");
            $attach=new Attach();
            $msg=$attach->uploadAvatar($id);
            $this->assign('msg',$msg);
        }
        $avatar=$user->where(array('id'=>$id))->field('avatar_large')->find();
        if($avatar['avatar_large'])
            $this->assign('avatar',$avatar);
        $this->assign('id',$id);
        $this->display();
    }
    private function lock(){
        $user=M('User');
        $msg="";
        foreach ($_POST['select'] as $key => $value) {
            $status=$user->field('nickname,forbidden')->find($value);
            if($status['forbidden']==1){
                $user->where(array('id'=>$value))->setField('forbidden',0);
                $msg.=$status['nickname']."成功解锁<br>";
            }else{
                $user->where(array('id'=>$value))->setField('forbidden',1);
                $msg.=$status['nickname']."已经被锁定<br>";
            }
        }
        XS('message',array(1,$msg));
        $this->redirect('index');
    }
    private function delete(){
    	$this->error('目前删除用户功能暂不开放');
    }
    public function add(){
        $this->display();
    }
    public function add_handle(){
        $validate = array(
            array('uid','require','必须输入用户ID！',1),
            array('nickname','require','必须输入用户姓名！',1),
            array('password','require','必须输入密码！',1),
            array('email','email','email格式不正确',2),
        );
        $user=M('user');
        $result=$user->validate($validate)->create();
        if (!$result){
            $this->error($user->getError());
        }else{
            if($userid=$user->add($result)){
                XS('message',array(1,"添加用户".$_POST['nickname']."成功"));
                $this->redirect('index');
            }else{
                $this->error("用户添加失败");
            }
        }
    }
}