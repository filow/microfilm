<?php

class UserModel extends Model{
	public function getUserInfo($userid=null,$limit="30")
	{
		if(is_null($userid)){
			$query=$this->limit($limit);
		}else{
			$query=$this->where(array('id'=>$userid));
		}
		$user=$this->field('password,msg_unread,notify_unread',true)
			->select();
		$user_belong=M('user_belong');
		$opus=M('opus');
		foreach ($user as $key => $value) {
			//性别处理
			if($value['sex']=='0') $user[$key]['sex']="男";
			else $user[$key]['sex']="女";
			// 作品数量
			$user[$key]['opus_count']=$opus->where(array('user_id'=>$value['id'],'status'=>0))->count();
		}
		return $user;
	}
}