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
		foreach ($user as $key => $value) {
			//性别处理
			if($value['sex']=='0') $user[$key]['sex']="男";
			else $user[$key]['sex']="女";
			//所属部门处理
			if($value['belong_type']=="1"){
				$belong_data=$user_belong->where(array('user_id'=>$value['id']))->find();
				$user[$key]['belong']=$belong_data;
			}
		}
		return $user;
	}
}