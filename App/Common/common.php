<?php

// 导入全局类
function importP($string){
	import('Class.' . $string, APP_PATH);
}
function passwordHash($uid,$pwd,$salt=""){
	$salt=$salt? $salt:C('PASSWORD_SALT');
	return md5($salt.$uid.$pwd);
}
/**
 * 增强的设置读取函数，可以调用数据库的设置
 * @param [string] $config_key [要查询的键值]
 */
function CF($config_key=null,$refresh=false){
	$moudule_name=CUT_GROUP_NAME;
	$configName=$moudule_name.'Config';
	//如果存在缓存，则调用缓存
	if($refresh || !($cache_data=S($configName))){
		$config=M('config');
		if($moudule_name=='Manage'){
			$query['range']=array('in','0,2');
		}else if($moudule_name=='Index'){
			$query['range']=array('in','0,1');
		}else{
			$query['range']=0;
		}
		$db_config_data=$config->where($query)->field('var,value')->select();
		$_config=array(); 
		foreach ($db_config_data as $key => $value) {
			//如果数据库中存储的是json格式的数组，则还原成数组
			if(0===strpos($value['value'],'{')){
				$value['value']=json_decode($value['value']);
			}
			$_config[strtoupper($value['var'])]=$value['value'];
		}
		$system_config=C();
		foreach ($system_config as $key => $value) {
			$_config[strtoupper($key)]=$value;
		}
		if(defined(APP_DEBUG)){
			S($configName,$_config,C('EXTERNAL_CONFIG_CACHE_LIFETIME_DEBUG'));
		}else{
			S($configName,$_config,C('EXTERNAL_CONFIG_CACHE_LIFETIME'));
		}
		$cache_data=$_config;
		unset($system_config,$_config,$db_config_data);
	}
	if($config_key){
		$upper_key=strtoupper($config_key);
		return isset($cache_data[$upper_key])? $cache_data[$upper_key] : 0 ;
	}else{
		return $cache_data;
	}
}
/**
 * 增强型SESSION控制函数
 * 可以对每一个键值设定过期时间
 * 使用缓存（memcache）缓存这些数据，因此过期时间较短，不能列出所有值，使用时应该存放临时变量
 * 所以只作为session函数的扩展，不能代替其用途
 * @param [string]  $key    [键名]
 * @param $val    [键值]
 * @param integer $expire [description]
 */
function XS($key,$val='',$expire=1800){
	$sessid=session_id();
	$s_key='XS'.$sessid.$key;
	//取值
	if($val===''){
		return S($s_key);
	}else if(is_null($val)){   //删除键值
		return S($s_key,null);
	}else{
		return S($s_key,$val,$expire);
	}
}

function atcLocation($attach_id){
	$attach=M('attach');
	$file=$attach->where(array('id'=>$attach_id))->find();

	if($file)
		return $file['file_location'];
	else
		return 0;
}
function getAvatar($attach_id){
	if(!$attach_id) return __ROOT__."/Public/images/user.jpg";
	$attach=M('attach');
	$file=$attach->where(array('id'=>$attach_id))->find();

	if($file){
		if(file_exists(".".$file['file_location'])){
			return __ROOT__.$file['file_location'];
		}else{
			return __ROOT__."/Public/images/user.jpg";
		}
	}else{
		return "";
	}
}

function getThumb($opus_id){
	$result="/Public/images/nothumb.jpg";
	if(!$opus_id) return $result;

	$opus=M('opus');
	$attach=M('attach');
	$opus_data=$opus->field("thumb")->find($opus_id);
	//如果用户上传过缩略图，那么就用缩略图
	if($opus_data['thumb']){
		$file=$attach->where(array('id'=>$opus_data['thumb']))->find();
		if($file){
			if(file_exists(".".$file['file_location'])){
				$result=$file['file_location'];
			}
		}
	}else{
		// 没有上传缩略图的情况，如果文件目录下存在缩略图就用缩略图
		importP('Attach');
		$video=Attach::getVideo($opus_id);
		if($video){
			$filename_withext=explode(".",basename($video['file_location']));
			$filename=$filename_withext[0].".jpg";

			$dir=dirname($video['file_location'])."/";
			$jpgfile=$dir.$filename;
			if(file_exists(".".$jpgfile)){
				$result=$jpgfile;
			}
		}
	}
	return __ROOT__.$result;
}

function getThumbMini($opus_id){
	$result="/Public/images/nothumb_mini.jpg";
	if(!$opus_id) return $result;

	$opus=M('opus');
	$attach=M('attach');
	$opus_data=$opus->field("thumb_mini")->find($opus_id);
	//如果用户上传过缩略图，那么就用缩略图
	if($opus_data['thumb_mini']){
		$file=$attach->where(array('id'=>$opus_data['thumb_mini']))->find();
		if($file){
			if(file_exists(".".$file['file_location'])){
				$result=$file['file_location'];
			}
		}
	}else{
		// 没有上传缩略图的情况，如果文件目录下存在缩略图就用缩略图
		importP('Attach');
		$video=Attach::getVideo($opus_id);
		if($video){
			$filename_withext=explode(".",basename($video['file_location']));
			$filename=$filename_withext[0]."_mini".".jpg";

			$dir=dirname($video['file_location'])."/";
			$jpgfile=$dir.$filename;
			if(file_exists(".".$jpgfile)){
				$result=$jpgfile;
			}
		}
	}
	return __ROOT__.$result;
}

function getClientIP() {
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
			$realip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) { 
			$realip = $_SERVER['HTTP_CLIENT_IP']; 
		} else { 
			$realip = $_SERVER['REMOTE_ADDR']; 
		}
	} else { 
		if (getenv("HTTP_X_FORWARDED_FOR")) { 
			$realip = getenv( "HTTP_X_FORWARDED_FOR"); 
		} elseif (getenv("HTTP_CLIENT_IP")) { 
			$realip = getenv("HTTP_CLIENT_IP"); 
		} else { 
			$realip = getenv("REMOTE_ADDR"); 
		} 
	} 
	return $realip; 
}

function can_upload(){
	$current=time();
	$from=intval(CF('UPLOAD_DATE_FROM'));
	$to=intval(CF('UPLOAD_DATE_TO'));
	if($current<=$to && $current>= $from){
		return 1;
	}else{
		return 0;
	}
}
function can_vote(){
	$current=time();
	$from=intval(CF('VOTE_DATE_FROM'));
	$to=intval(CF('VOTE_DATE_TO'));
	if($current<=$to && $current>= $from){
		return 1;
	}else{
		return 0;
	}
}
function can_judge(){
	$current=time();
	$from=intval(CF('VOTE_PROFESSOR_DATE_FROM'));
	$to=intval(CF('VOTE_PROFESSOR_DATE_TO'));
	if($current<=$to && $current>= $from){
		return true;
	}else{
		return false;
	}
}