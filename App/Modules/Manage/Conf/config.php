<?php
return array(
    //用户验证设置
    // 'RBAC_SUPERADMIN' => 'sman', //超级管理员名称
    'RBAC_ALLOW_ALL' => '_ALLOW_ALL',
    'USER_AUTH_ON' => true,
    'USER_AUTH_KEY' => 'admin_id',  //认证识别号
    'RBAC_ROLE_TABLE' => 'rbac_role',
    'RBAC_USER_TABLE' => 'rbac_to_role',
    'RBAC_ACCESS_TABLE' => 'rbac_access',
    'RBAC_NODE_TABLE' => 'rbac_node',
    'NAV_KEY' => '_NAV_',  //用户权限列表的SESSION键名
    'PMS_KEY' => '_PERMISSION_',
    'APP_AUTOLOAD_PATH' => '@.TagLib',
    'TAGLIB_BUILD_IN' => 'Cx,Config', 
	);