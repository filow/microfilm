<?php

class PermissionModel extends Model{
	public function getAllUserRole(){
		$db=M();
        $table  = array('role'=>CF('DB_PREFIX').CF('RBAC_ROLE_TABLE'),
               'user'=>CF('DB_PREFIX').CF('RBAC_USER_TABLE'),
               'admin'=>CF('DB_PREFIX').'admin');
        $sql = "select role.id as role_id,role.status,role.remark,admin.id as uid from ".
            $table['role']." as role,".
            $table['user']." as user,".
            $table['admin']." as admin ".
            "where admin.id=user.user_id and user.role_id=role.id and role.status=1";
        $role_data= $db->query($sql);
        return $role_data;
	}
    public function getRoleAccess($role_id){
        // Db方式权限数据
        $db     = M();
        $table  = array('role'=>CF('DB_PREFIX').CF('RBAC_ROLE_TABLE'),
                       'user'=>CF('DB_PREFIX').CF('RBAC_USER_TABLE'),
                       'access'=>CF('DB_PREFIX').CF('RBAC_ACCESS_TABLE'),
                       'node'=>CF('DB_PREFIX').CF('RBAC_NODE_TABLE'));
        $sql = "select node.id,node.title,node.is_nav from ".
            $table['access']." as access,".
            $table['node']." as node ".
            "where access.role_id='{$role_id}' and access.node_id=node.id ".
            "and node.pid=0 order by node.sort";
        $modules =   $db->query($sql);
        // 依次读取模块的操作权限
        $data=array();
        foreach($modules as $key=>$module) {
            $moduleId    =   $module['id'];
            $moduleName = $module['name'];
            $sql = "select node.id,node.title,node.is_nav from ".
                $table['access']." as access,".
                $table['node']." as node ".
                "where access.role_id='{$role_id}' and access.node_id=node.id ".
                "and node.pid='{$moduleId}' order by node.sort";
            $action =   $db->query($sql);
            $data[$key]=$action;
            $data[$key]['_self']=$module;
        }
        return $data;
    }
    public function getAllNode(){
        $node=M(CF('RBAC_NODE_TABLE'));
        $module = $node->where(array('pid'=>0))->order('sort')->select();
        $result=array();
        foreach ($module as $key => $value) {
            $action=$node->where(array('pid'=>$value['id']))->order('sort')->select();
            $result[$key]=$action;
            $result[$key]['_self']=$value;
        }
        return $result;
    }
    public function getUserAccess($user_id){
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
                    "where user.user_id='{$user_id}' and user.role_id=role.id ".
                    "and access.role_id=role.id ".
                    "and role.status=1 and access.node_id=node.id and ".
                    "node.pid=0 order by node.sort";
        $modules =   $db->query($sql);
        // 依次读取模块的操作权限
        $data=array();
        foreach($modules as $key=>$module) {
            $moduleId    =   $module['id'];
            $moduleName = $module['name'];
            $sql   =  "select node.id,node.name,node.title,node.icon,node.is_nav from ".
                $table['role']." as role,".
                $table['user']." as user,".
                $table['access']." as access ,".
                $table['node']." as node ".
                "where user.user_id='{$user_id}' and user.role_id=role.id ".
                "and access.role_id=role.id ".
                "and role.status=1 and access.node_id=node.id and ".
                "node.pid={$moduleId} order by node.sort";
            $action =   $db->query($sql);
            $data[$key]=$action;
            $data[$key]['_self']=$module;
        }
        return $data;
    }
}