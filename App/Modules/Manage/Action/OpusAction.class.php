<?php 
class OpusAction extends CommonAction{
	public function index(){
		$opus=M('opus');
		$opus_list=$opus->order('id desc')->where(array('init'=>0))->select();
		foreach ($opus_list as $key => $value) {
			$opus_list[$key]['authors']=$this->getAuthorList($value['id']);
		}
		$this->assign('opus',$opus_list);
		$this->display();
	}
	public function opusInfo(){
		importP('Attach');
		$id=I('opus_id');
		if(!$id) $this->error("没有选择视频！");

		$opus=M('opus');
		$opus_info=$opus->find($id);
		$user_info=$this->getAuthorList($id);

		$video=Attach::getVideo($id);
		$video['download_filename']=urlencode("[微课程大赛] ".$video['filename']);
		$this->assign("opus_info",$opus_info);
		$this->assign("user_info",$user_info);
		$this->assign('video',$video);
		$this->display();
	}
	public function reupload(){
		$id=I('opus_id');
		if(!$id) $this->error("没有选择视频！");

		importP('Attach');
		$has_uploaded=Attach::getVideo($id);
		$this->assign('old_video',$has_uploaded);

		$this->display();
	}
	public function uploadVideo(){
		set_time_limit(0);
		$verifyToken = md5('MC_OPUS_VIDEO_UPLOAD'. $_POST['opus_id'] . $_POST['timestamp']);
		if($verifyToken==$_POST['token']){
			importP('Attach');
		    $attach=new Attach();
		    $result=$attach->uploadVideo($_POST['opus_id']);
	    	echo json_encode($result);
		}else{
			echo json_encode(array(0,"页面认证失败！"));
		}
	}
	private function getAuthorList($opus_id){
        $authors=M('opus_author')->where(array('opus_id'=>$opus_id))->field('author')->select();
        if(!$authors) return false;
        $str="";
        foreach ($authors as $key => $value) {
            $str.=$value['author']."&nbsp;&nbsp;&nbsp;";
        }
        return $str;
    }
	public function download(){
		$secret=$_GET['secret'];
		$filename=$_GET['fn'];
		if(!$secret){header("HTTP/1.1 404 Not Found");exit(0);}
		if(!$filename){$this->error('文件名错误！');exit(0);}
		$filename=urldecode($filename);
		$secret=addslashes($secret);
		$attach=M('attach');
		$info=$attach->where(array('access_key'=>$secret))->find();
		if(!$info){header("HTTP/1.1 404 Not Found");exit(0);}
		import('ORG.Net.Http');
		Http::download('.'.$info['file_location'],$filename,'',3600);
	}

	public function votemanage(){
		importP('Page');

		$vote=CF('DB_PREFIX')."opus_vote";
		$opus=CF('DB_PREFIX')."opus";

		$count=M('opus_vote')->count();
		$page = new Page($count,50);
		$sql="select vote.*,opus.opus_name from {$vote} as vote,{$opus} as opus ".
		     "where vote.opus_id=opus.id ";
		if(isset($_GET['filter'])){
			$_GET['filter']=mysql_escape_string($_GET['filter']);
			$sql.="and opus.opus_name like '%".$_GET['filter']."%' ";
		}
		$sql.="order by vote.id desc limit {$page->firstRow},{$page->listRows}";
		$vote_data=M()->query($sql);
		$count=1;
		foreach ($vote_data as $key => $value) {
			$vote_data[$key]["order"]=$page->firstRow+$count++;
			if(!strpos($value['user_ip'],".")){
				$vote_data[$key]['user_ip']=long2ip($value['user_ip']);
			}
		}
		$this->assign('show',$page->show());
		$this->assign('vote_data',$vote_data);
		$this->display();
	}
	public function deleteVote(){
		$vote=M('opus_vote');
		$deletecount=0;
		foreach ($_POST['vote_list'] as $key => $value) {
			$status=$vote->where(array('id'=>$value))->delete();
			if($status) $deletecount++;
		}
		$this->success('共删除'.count($_POST['vote_list'])."个投票，其中成功".$deletecount."个");
	}
}