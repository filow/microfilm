<?php 
class PassageAction extends CommonAction{
	public function _empty($name){
		$id=$name;
		$notify=M('notification');
		$query=array(
					'valid_from'=>array('lt',time()),
					'id'=>$id
					);
		$data=$notify->where($query)->find();
		if(!$data){
			$this->error('您所请求的文章不存在！');
		}else{
			$this->assign('passage',$data);
			$this->display('article');
		}
	}
}