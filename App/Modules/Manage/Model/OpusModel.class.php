<?php 
class OpusModel extends Model{
	public function getVideoList(){
		importP('Attach');
		$query=array(
			'status'  => 0,
			'youkuid' => array('exp',' is null '),
			);
		$result=$this->where($query)->order('id asc')->field('id,user_id,opus_name,add_time,overview')->select();
		return $result;
	}

	public function getUserName($user_id)
	{
		$user=M('user');
		$result=$user->find($user_id);
			return $result['nickname'];
	}


}