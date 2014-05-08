<?php
class UserModel extends Model{
	public function getOpus($userid=0){
		if(!$userid) return false;
		$opus=M('opus');
		$opus_data=$opus->where(array('user_id'=>$userid))->order('add_time desc')->select();
		if(!$opus_data) return false;
		return $opus_data;
	}
	public function getVoteOpus($userid=0,$limit=""){
		if(!$userid) return false;
		$opus=M('opus');
		$vote=M('opus_vote');
		$list=$vote->where(array('user_id'=>$userid))->limit($limit)->order('date desc')->select();
		$result=array();
		foreach ($list as $key => $value) {
			$opus_data=$opus->where(array('id'=>$value['opus_id']))->find();
			$nickname=$this->where(array('id'=>$value['user_id']))->field('nickname')->find();
			$opus_data['nickname']=$nickname['nickname'];
			$opus_data['vote_date']=$value['date'];
			$result[]=$opus_data;
			unset($opus_data);
		}
		return $result;
	}
	public function getMessage($userid=0,$limit=""){
		if(!$userid) return false;
		$msg=M('msg');
		$data=$msg->where(array('user_id'=>$userid))->order('id desc')->limit($limit)->select();
		return $data;
	}
	public function refreshMessage($userid=0){
		if(!$userid) return false;
		$msg=M('msg');
		$count=$msg->where(array('user_id'=>$userid,'has_read'=>0))->count();
		$user=M('user');
		$user->where(array('id'=>$userid))->setField('msg_unread',$count);
	}
	public function getCommentByUserID($userid=0,$limit=""){
		if(!$userid) return false;
		$comment=M('opus_comment');
		$opus=M('opus');
		$db=M();
		$opus=CF('DB_PREFIX')."opus";
		$comment=CF('DB_PREFIX')."opus_comment";
		$sql="select cmt.*,opus.opus_name,opus.thumb from ".
			 "{$opus} as opus,{$comment} as cmt ".
			 "where cmt.user_id={$userid} and opus.id=cmt.opus_id ORDER BY  `cmt`.`date` DESC ";
		if($limit){
			$sql.="limit {$limit}";
		}
		$result=$db->query($sql);
		return $result;
	}
	public function getRankList(){
		import('ORG.Util.String');
		$data=$this->where(array('forbidden'=>0))->order('popularity desc,id asc')->limit(7)->field('id,nickname,department,popularity,avatar,uid')->select();
		foreach ($data as $key => $value) {
			$data[$key]['nickname']=String::msubstr($value['nickname'],0,5,'utf-8',false);
			$data[$key]['department']=String::msubstr($value['department'],0,6,'utf-8',false);
		}
		return $data;
	}
	public function getUserInfo($userid=null){
		$user=$this->where(array('id'=>$userid))->find();
		return $user;
	}
}