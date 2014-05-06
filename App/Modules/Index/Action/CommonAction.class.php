<?php
class CommonAction extends Action {
	function _initialize(){

		// 访问量统计
		$visit=intval(CF('VISIT_COUNT'));
		$visit_inc=S('VISIT_INC');
		if(!$visit_inc){
			S('VISIT_INC',1);
			S('VISIT_COUNT',$visit+1);
		}else{
			S('VISIT_INC',$visit_inc+1);
			S('VISIT_COUNT',$visit+$visit_inc);
		}
		if($visit_inc%50==0) 
			$config=M('config')->where(array('var'=>'VISIT_COUNT'))->setInc('value',50);

		// 运行时间统计
        G('beginTime',$GLOBALS['_beginTime']);
        G('viewEndTime');
        $this->assign('loadtime',G('beginTime','viewEndTime'));
        // 在线人数统计
        $online_count=M('session')->count();
        $this->assign('online',$online_count);

	}
}