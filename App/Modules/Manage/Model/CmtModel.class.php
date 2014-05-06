<?php

class CmtModel extends Model{
	public function getCmtInfo($cmtid=null,$limit="30"){
		$db=M('opus_comment');
		if(is_null($cmtid)){
			$query=$db->limit($limit);
		}else{
			$query=$db->where(array('id'=>$cmtid));
		}
		$cmt=$db->order('id desc')->select();
		$belong_user=M('user');
		$belong_opus=M('opus');
		foreach ($cmt as $key => $value) {
			//所属作品处理
			$belong_opus_data=$belong_opus->where(array('id'=>$value['opus_id']))->field('id,opus_name')->find();
			$cmt[$key]['opus']=$belong_opus_data;
			//所属用户处理
			$belong_user_data=$belong_user->where(array('id'=>$value['user_id']))->field('id,nickname')->find();
			$cmt[$key]['user']=$belong_user_data;
		}
		return $cmt;
	}
	public function getCmtByFilter($opus_id=null,$user_id=null){
		$cmt=M('opus_comment');
		if($opus_id || $user_id){
			if(I('opus_id')) $filter['opus_id']=I('opus_id');
			if(I('user_id')) $filter['user_id']=I('user_id');        
            $data=$cmt->where($filter)->order('id desc')->select();
    		$belong_user=M('user');
			$belong_opus=M('opus');
			foreach ($data as $key => $value) {
				//所属作品处理
				$belong_opus_data=$belong_opus->where(array('id'=>$value['opus_id']))->field('id,opus_name')->find();
				$data[$key]['opus']=$belong_opus_data;
				//所属用户处理
				$belong_user_data=$belong_user->where(array('id'=>$value['user_id']))->field('id,nickname')->find();
				$data[$key]['user']=$belong_user_data;
			}
			return $data;
		}else{
			return 0;
		}
	}
}