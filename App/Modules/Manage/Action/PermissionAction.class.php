<?php
class PermissionAction extends CommonAction {
    public function index(){
        // 用户信息和角色
    	$pms=D('Permission');
    	$admin=M('admin');
    	$admin_list=$admin->field('password',true)->select();
    	$role_list=$pms->getAllUserRole();
    	foreach ($admin_list as $a_k => $a_v) {
    		foreach ($role_list as $r_v) {
    			if($r_v['uid']==$a_v['id']){
    				unset($r_v['uid']);
    				$admin_list[$a_k]['role'][]=$r_v;
    			}
    		}
    	}
        $this->assign('admin_list',$admin_list);

        //角色列表
        $role=M(CF('RBAC_ROLE_TABLE'));
        $role_list=$role->select();
        $this->assign("role_list",$role_list);

    	$this->display();
    }

    public function ajax_getRoleAccess(){
        $role_id=ajax_check('role_id');
        if(!CPM('view_role')) ajax_err(-3,"Permission_Denied","用户权限不足");
        $pms=D('Permission');
        $node=$pms->getAllNode();
        $access=$pms->getRoleAccess($role_id);
        foreach ($node as $key => $value) {
            foreach ($value as $sub_key => $sub_node) {
                $node[$key][$sub_key]['owned']=0;
                foreach ($access as $item) {
                    foreach ($item as $acc_key => $acc_val) {
                        if($acc_val['id'] == $sub_node['id']){
                            $node[$key][$sub_key]['owned']=1;
                            break 2;
                        }
                    }
                }
            }
        }
    	$this->ajaxReturn($node);
    } 
    public function ajax_getUserInfo(){
        $user_id=ajax_check('user_id');
        if(!CPM('modify_user')) ajax_err(-3,"Permission_Denied","用户权限不足");
        $admin=M('admin');
        $admin_data=$admin->where(array('id'=>$user_id))->field('password',true)->find();
        $this->ajaxReturn($admin_data);
    } 
    public function ajax_getRoleAll(){
        if(!CPM('modify_user')) ajax_err(-3,"Permission_Denied","用户权限不足");
        $role=M(CF('RBAC_ROLE_TABLE'));
        $data=$role->where(array('status'=>1))->select();
        $this->ajaxReturn($data);
    }
    public function modifyUserHandle(){
        if(!CPM('modify_user')) $this->error("用户权限不足");
        $admin=M('admin');
        $data=$this->_post();
        $origin_data=$admin->where(array('id'=>$data['id']))->find();
        if(!$origin_data){
            $msg=array(0,"未找到该用户");
            XS('message',$msg,60);
            $this->redirect('Index');
        }
        //密码处理
        if(isset($data['password']) && $data['password']){
            $data['password']=passwordHash($origin_data['uid'],$data['password']);
        }else{
            unset($data['password']);
        }
        //启用处理
        if(isset($data['is_forbidden']) && $data['is_forbidden']=="on"){
            $data['is_forbidden']=0;
        }else{
            $data['is_forbidden']=1;
        }
        //角色处理
        $uid=$data['id'];
        foreach($data['role'] as $k => $v){
            $role_data[$k]['user_id']=$uid;
            $role_data[$k]['role_id']=$v;
        }
        unset($data['role']);  //释放role键
        //删除所有原用户角色
        $role_user=M(CF('RBAC_USER_TABLE'));
        $role_user->where(array('user_id'=>$uid))->delete();
        //添加角色信息
        if($role_user->addAll($role_data)){
            $msg=array(1,"职位变更成功");
        }else{
            $msg=array(0,"职位变更失败或已清空");
        }
        //变更用户信息
        $admin=M('admin');
        if($admin->save($data)){
            $msg=array($msg[0]|1,$msg[1]."<br />用户信息变更成功");
        }else{
            $msg=array($msg[0]|0,$msg[1]."<br />用户信息变更失败或没有更新");
        }
        XS('message',$msg,60);
        $this->redirect('Index');
    }
    public function addUserHandle(){
        if(!CPM('add_admin')) $this->error("您没有添加管理员的权限");
        $validate = array(
            array('uid','require','管理员ID已经存在！',1,'unique'),
            array('realname','require','管理员姓名已经存在！',1,'unique'),
            array('password','require','必须填写密码',1,''),
         );
        $admin=M('admin');
        $data=$admin->validate($validate)->create();
        if($data){
            //密码处理
            $data['password']=passwordHash($data['uid'],$data['password']);
        }else{
            $this->error($admin->getError());
        }
        $role=$_POST['role'];unset($_POST['role']);
        //变更用户信息
        if($uid=$admin->add($data)){
            //角色处理
            foreach($role as $k => $v){
                $role_data[$k]['user_id']=$uid;
                $role_data[$k]['role_id']=$v;
            }
            unset($data['role']);  //释放role键
            //添加角色信息
            $role_user=M(CF('RBAC_USER_TABLE'));
            $role_user->addAll($role_data);
            XS('message',array(1,'用户添加成功'),60);
            $this->redirect('index');
        }else{
            $this->error("用户添加失败");
        }
    }
    public function deleteUser()
    {
        $uid=I('id',0,'intval');
        $admin=M('admin');
        if($admin->where(array('id'=> $uid))->delete())
            XS('message',array(1,'删除成功'),60);
        else
            XS('message',array(0,'删除失败'),60);
        $this->redirect('index');
    }
    public function node_list(){
        $pms=D('Permission');
        $node=$pms->getAllNode();
        if(I('role_id')){
            $access=$pms->getRoleAccess(I('role_id'));
            $this->assign('edit_role',1);
        }else{
            $access=$pms->getUserAccess($_SESSION['id']);
        }
        foreach ($node as $key => $value) {
            foreach ($value as $sub_key => $sub_node) {
                $node[$key][$sub_key]['owned']=0;
                foreach ($access as $item) {
                    foreach ($item as $acc_key => $acc_val) {
                        if($acc_val['id'] == $sub_node['id']){
                            $node[$key][$sub_key]['owned']=1;
                            break 2;
                        }
                    }
                }
            }
        }
        // dump($node);die;
        $this->assign("node",$node);
        $this->display();
    }
    public function access_modify(){
        $rid=I('id',0,'intval');
        if(!$rid) $this->error('没有传入rid');

        $access=M(CF('RBAC_ACCESS_TABLE'));
        $access->where(array('role_id' => $rid))->delete();
        $data=array();
        foreach($_POST['access'] as $v){
            $data[]=array(
                'role_id' => $rid,
                'node_id' => $v,
            );
        }
        if($access->addAll($data)){
            XS('message',array(1,'角色信息修改成功'),60);
        }else{
            XS('message',array(0,'角色信息修改失败'),60);
        }
        $this->redirect('index');
    }
    public function addRole(){
        if(!CPM('add_role')) $this->error("用户权限不足");
        $role=M(CF('RBAC_ROLE_TABLE'));
        $role_data['remark']=I('remark');
        $role_data['status']=1;
        if($role_data['remark']==""){
            $msg=array(0,'职位名称不能为空');
            XS('message',$msg,60);
            $this->redirect('Index');
        }
        if($role->add($role_data)){
            $msg=array(1,'职位添加成功');
        }else{
            $msg=array(0,'职位添加失败');
        }
        XS('message',$msg,60);
        $this->redirect('Index');
    }
    public function editRole()
    {
        if(!CPM('edit_role')) $this->error("用户权限不足");
        $role=M(CF('RBAC_ROLE_TABLE'));
        $role_data['id']=I('id');
        $role_data['remark']=I('remark');
        $role_data['status']=I('status');
        if(isset($role_data['status']) && $role_data['status']=="on"){
            $role_data['status']=1;
        }else{
            $role_data['status']=0;

        }
        if($role->save($role_data)){
            $msg=array(1,'职位信息保存成功');
        }else{
            $msg=array(0,'职位信息保存失败');
        }
        XS('message',$msg,60);
        $this->redirect('Index');
    }
    public function deleteRole(){
        if(!CPM('delete_role')) $this->error("用户权限不足");
        $role=M(CF('RBAC_ROLE_TABLE'));
        $id=I('id');
        if($role->where(array('id'=>$id))->delete()){
            $msg=array(1,'删除成功');
        }else{
            $msg=array(0,'删除失败');
        }
        XS('message',$msg,60);
        $this->redirect('Index');
    }
}