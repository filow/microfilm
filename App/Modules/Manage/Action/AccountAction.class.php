<?php
class AccountAction extends CommonAction {
  public function index(){
    $info=M('admin')->find(session('id'));
    $this->assign('info',$info);
    $this->display();
  }
  public function editInfo(){
    $admin=M('admin');
    $info=$admin->find(session('id'));
    if(!$info){
      $this->error('找不到这个用户！');
    }
    if(!$_POST['password']){
      unset($data['password']);
    }else{
      $data['password']=passwordHash($info['uid'],$_POST['password']);
    }

    $data['realname']=mysql_escape_string($_POST['realname']);
    $admin->where(array('id'=>$info['id']))->save($data);
    $this->success('保存成功！');
    dump($data);
  }
}