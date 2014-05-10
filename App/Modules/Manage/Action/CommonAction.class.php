<?php
class CommonAction extends Action {
    function _initialize()
    {
    	   //判断是否登录
        if(!isset($_SESSION[CF('USER_AUTH_KEY')]))
            $this->redirect("Public/login");
        if(!$this->checkPermission())
            $this->error('您没有访问此功能的权限!');
        $module=ucfirst(strtolower(MODULE_NAME));
        $action=ucfirst(strtolower(ACTION_NAME));
        $title=$_SESSION[CF('NAV_KEY')][$module][$action][0];
        $this->assign('title',$title);

        if(XS('message')){
            $this->assign('msg',XS('message'));
        }

        if($this->checkPermission('Account','Index')){
            $this->assign("AccountManage",1);
        }
    }
    function checkPermission($action=ACTION_NAME,$module=MODULE_NAME){
        //不开启验证时直接放行
        if(!CF('USER_AUTH_ON') || session(CF('RBAC_ALLOW_ALL'))){
            return 1;
        }
        //查询是否存在系统权限节点列表
        $nodelist=S('_BACKYARD_NODELIST_');
        //如果在调试阶段则无论如何都刷新缓存
        if(!$nodelist){
            $admin=D('Admin');
            $nodelist=$admin->saveAllList(2);
            if(APP_DEBUG){
                S('_BACKYARD_NODELIST_',$nodelist,60);
            }else{
                S('_BACKYARD_NODELIST_',$nodelist,300);
            }
        }
        $module=ucfirst(strtolower($module));
        $action=ucfirst(strtolower($action));
        if(in_array($action,$nodelist[$module])){
            $pms_data=session(CF('PMS_KEY'));
            return in_array($action, $pms_data[$module]);
        }else{
            return 1;
        }
    }
}