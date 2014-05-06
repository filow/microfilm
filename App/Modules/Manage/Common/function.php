<?php
define('CUT_GROUP_NAME','Manage');
function node_merge($node,$pid=0){
    $arr= array();
    foreach($node as $v){
        if($v['pid']==$pid){
            $temp=node_merge($node,$v['id']);
            if($temp) $v['child']=$temp;
            $arr[]=$v;
        }
    }
    return $arr;
}

function ajax_err($err_code,$err_en,$err_ch){
	echo json_encode(array($err_code,$err_en,$err_ch));
	exit();
}

function ajax_check($para=null){
	if(!IS_AJAX && CF('AJAX_VERIFY')){
		ajax_err(-1,"Request_Method_Fault","请求方式错误");
	}
	if(!is_null($para)){
		if(!I($para)){
			ajax_err(-2,"Request_Param_Fault","请求变量传递错误");
		}else{
			return I($para);
		}
	}
}
/**
 * 检测当前用户是否拥有指定权限
 * @param  [string] $module [模块名]
 * @param  [string] $action [动作名]
 * @return [bool]         [结果]
 */
function CPM($action=ACTION_NAME,$module=MODULE_NAME){
	//不开启验证时直接放行
    if(!CF('USER_AUTH_ON') || session(CF('RBAC_ALLOW_ALL'))){
        return 1;
    }
    $module=ucfirst(strtolower($module));
    $action=ucfirst(strtolower($action));
	$pms_data=session(CF('PMS_KEY'));
	return in_array($action, $pms_data[$module]);
}

function msg($code=0,$message="",$redirect=NULL){
    XS('message',array($code,$message));
    if(!is_null($redirect)){
        redirect(U($redirect),0,"");
    }
}

function sess($key){
    return "";
}
?>