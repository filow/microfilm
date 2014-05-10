<?php 
class OpusModel extends Model{
	public function getUserName($user_id)
	{
		$user=M('user');
		$result=$user->find($user_id);
			return $result['nickname'];
	}


}