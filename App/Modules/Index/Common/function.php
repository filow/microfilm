<?php  
define('CUT_GROUP_NAME','Index');
function sess($key,$value=NULL){
	// 删除该键
	if($value===""){
		unset($_SESSION['_INDEX_'][$key]);
	}else{
		// 判断是否含有子代选择符
		$string=explode('.',$key);
		if(count($string)>=2){
			if($value===NULL){
				return $_SESSION['_INDEX_'][$string[0]][$string[1]];	
			}else{
				$_SESSION['_INDEX_'][$string[0]][$string[1]]=$value;
			}
		}else{
			if($value===NULL){
				return $_SESSION['_INDEX_'][$string[0]];	
			}else{
				$_SESSION['_INDEX_'][$string[0]]=$value;
			}
		}
		
	}

}

function getVoteStatus($opusid,$userip=0){
	$opus_vote=M('opus_vote');
	if(!$userip) $userip=getClientIP();
	$query=array(
		'opus_id'=>$opusid,
		'user_ip'=>array('like',substr($userip,0,strrpos($userip,"."))."%"),
		);
	if($opus_vote->where($query)->count())
		return 1;
	else
		return 0;
}

function islogged(){
	return isset($_SESSION['_INDEX_']['acc']['id']);
}

/**
 * 计算给定时间戳与当前时间相差的时间，并以一种比较友好的方式输出
 * @param  [int] $timestamp [给定的时间戳]
 * @param  [int] $current_time [要与之相减的时间戳，默认为当前时间]
 * @return [string]            [相差天数]
 */
function tmspan($timestamp,$current_time=0){
	if(!$current_time) $current_time=time();
	$span=$current_time-$timestamp;
	if($span<60){
		return "刚刚";
	}else if($span<3600){
		return intval($span/60)."分钟前";
	}else if($span<24*3600){
		return intval($span/3600)."小时前";
	}else if($span<(7*24*3600)){
		return intval($span/(24*3600))."天前";
	}else{
		return date('Y-m-d',$timestamp);
	}
}


function getdown($secret){
	if($secret){
		return U('/Download')."?secret={$secret}";
	}else{
		return "javascript:alert('没有找到该文件！')";
	}
}

function viewOpus($opus_id){
	$opus=M('opus');
	$opus_view=M('opus_view');
	
	$userip=getClientIP();
	$query=array(
		'opus_id'=>intval($opus_id),
		'user_ip'=>array('like',substr($userip,0,strrpos($userip,"."))."%"),
		'time'=>array('gt',time()-3600)
		);
	if($opus_view->where($query)->count()){
		return;
	}else{
		$data=array(
			'opus_id'=>intval($opus_id),
			'ip'=>$userip,
			'time'=>time()
		);
		$opus_view->add($data);
		$count=$opus_view->where(array('opus_id'=>$opus_id))->count();
		$opus->where(array('id'=>$opus_id))->setField('view_count',$count);
	}
}