<?php

class AdminModel extends Model{
	protected $table_name='admin';
    //用户数据
    public $uid='';
    public $pwd='';
    //要操作的对象实例
	private $instance;
    //是否已经通过验证
    private $checked=0;
    private $accountData=array();
	function __construct($uid='',$pwd=''){
		$this->instance = new Model($this->table_name);
        $this->uid=$uid;
        $this->pwd=$pwd;
    }
    public function checkAccount($uid=null,$pwd=null){
        $uid = is_null($uid)?$this->uid : $uid;
        $pwd = is_null($pwd)?$this->pwd : $pwd;
    	$pwd_hash=passwordHash($uid,$pwd);
    	$result=$this->instance->where(array('uid'=>$uid,'password'=>$pwd_hash))->find();
    	if(!$result){
    		return 0;
    	}else if($result['is_forbidden']=='1'){
    		return -1;
    	}else{
            $this->checked=1;
            $this->accountData=$result;
    		return $result;
    	}
    }
    public function setAccountData(){
        if($this->checked){
            foreach ($this->accountData as $key => $value) {
                if(in_array($key, array('password'))) continue;
                $_SESSION[$key]=$value;
            }
            $_SESSION['login_time']=time();
            $_SESSION[CF('USER_AUTH_KEY')]=$this->accountData['uid'];
            $this->saveAccessList();
            return 1;
        }else{
            return 0;
        }
    }
    public function saveAccessList(){
        if(!isset($_SESSION[CF('NAV_KEY')])){
            //如果是超级管理员或者没开启开关,则不验证,且给予所有节点列表
            if(!CF('USER_AUTH_ON') ||
                in_array(session(CF('USER_AUTH_KEY')), explode(',',CF('RBAC_SUPERADMIN')))){
                //给予所有权限
                session(CF('RBAC_ALLOW_ALL'),true);
                $this->saveAllList();
            }else{
                $this->saveList(session('id'));
            }
        }
    }
    public function saveAllList($need_return=0){
        $nodes=M(CF('RBAC_NODE_TABLE'));
        // 读取模块信息
        $module=$nodes->where(array('pid' => 0))
                ->field('id,name,title,icon,is_nav')->order('sort')->select();
        //读取控制器信息
        $nav_data=array();
        $pms_data=array();
        foreach($module as $mod_val){
            $moduleName=ucfirst(strtolower($mod_val['name']));
            $action=$nodes->where(array('pid' => $mod_val['id']))
                    ->field('id,name,title,icon,is_nav')->order('sort')->select();

            //重组成菜单列表和权限列表
            if($action){
                foreach($action as $act_val){
                    $actionName=ucfirst(strtolower($act_val['name']));
                    //权限列表
                    $pms_data[$moduleName][]=$actionName;
                    //菜单列表
                    if($act_val['is_nav']==1)
                        $nav_tmp[$actionName]=array($act_val['title'],$act_val['icon']);
                }
            }
            if(isset($nav_tmp)) $nav_data[$moduleName]=$nav_tmp;
            $nav_data[$moduleName]['MODULE_TITLE']=$mod_val['title'];
            $nav_data[$moduleName]['MODULE_ICON']=$mod_val['icon'];
            unset($nav_tmp);
        }
        if(!$need_return){
            session(CF('NAV_KEY'),$nav_data);
            session(CF('PMS_KEY'),$pms_data);
            return;
        }else{
            if($need_return==1)
                return $nav_data;
            else
                return $pms_data;
        }

    }
    /**
     * 取得指定用户的所有权限列表
     * @param integer $authId 用户ID
     * @access public
     */
    static public function saveList($authId,$need_return=0) {
        // Db方式权限数据
        $db     = M();
        $table  = array('role'=>CF('DB_PREFIX').CF('RBAC_ROLE_TABLE'),
                       'user'=>CF('DB_PREFIX').CF('RBAC_USER_TABLE'),
                       'access'=>CF('DB_PREFIX').CF('RBAC_ACCESS_TABLE'),
                       'node'=>CF('DB_PREFIX').CF('RBAC_NODE_TABLE'));
        $sql    =   "select node.id,node.name,node.title,node.icon,node.is_nav from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id ".
                    "and access.role_id=role.id ".
                    "and role.status=1 and access.node_id=node.id and ".
                    "node.pid=0 order by node.sort";
        $modules =   $db->query($sql);
        // 依次读取模块的操作权限
        $nav_data=array();
        $pms_data=array();
        foreach($modules as $key=>$module) {
            $moduleId    =   $module['id'];
            $moduleName = ucfirst(strtolower($module['name']));
            $sql   =  "select node.name,node.title,node.icon,node.is_nav from ".
                $table['role']." as role,".
                $table['user']." as user,".
                $table['access']." as access ,".
                $table['node']." as node ".
                "where user.user_id='{$authId}' and user.role_id=role.id ".
                "and access.role_id=role.id ".
                "and role.status=1 and access.node_id=node.id and ".
                "node.pid={$moduleId} order by node.sort";
            $action =   $db->query($sql);
            //重组成菜单列表和权限列表
            foreach($action as $act_val){
                $actionName=ucfirst(strtolower($act_val['name']));
                //权限列表
                $pms_data[$moduleName][]=$actionName;
                //菜单列表
                if($act_val['is_nav']==1)
                    $nav_tmp[$actionName]=array($act_val['title'],$act_val['icon']);
            }
            $nav_data[$moduleName]=$nav_tmp;
            $nav_data[$moduleName]['MODULE_TITLE']=$module['title'];
            $nav_data[$moduleName]['MODULE_ICON']=$module['icon'];
            unset($nav_tmp);
        }
        if(!$need_return){
            session(CF('NAV_KEY'),$nav_data);
            session(CF('PMS_KEY'),$pms_data);
            return;
        }else{
            if($need_return==1)
                return $nav_data;
            else
                return $pms_data;
        }
    }


}