<?php
class OpusAction extends CommonAction{
	public function index()
	{
		$this->redirect('gallery');
	}
	/**
	 * 作品展示
	 * @return [type] [description]
	 */
	public function gallery(){
		// 处理作品筛选
		$order=I('order');
		$query="";
		switch ($order) {
			case 'vote':
				$query="vote_count desc,";break;
			case 'view':
				$query="view_count desc,";break;
			case 'all':
				$query="popularity desc,";break;
			case 'comment':
				$query="comment_count desc,";break;
		}
		$query.="add_time desc";


		importP('Page');
		import('ORG.Util.String');
		$opus=M('opus');
		$user=M('user');

		// 数据获取
		$count=$opus->where(array('status'=>0))->count();
		$page = new Page($count,20);
		$opus_data=$opus->where(array('status'=>0))->order($query)->field('content',true)->limit($page->firstRow.','.$page->listRows)->select();
		foreach ($opus_data as $key => $value) {
			$opus_data[$key]['overview']=String::msubstr($value['overview'],0,80,'utf-8',false);
			$user_data=$user->where(array('id'=>$value['user_id']))->field('uid,nickname,department,avatar')->find();
			$opus_data[$key]['userinfo']=$user_data;
			unset($user_data);
		}
		$this->assign('show',$page->show());
		$this->assign('count',$count);
		$this->assign('opus',$opus_data);
		$this->display();
	}

	public function view(){
		$id=I('id','intval');
		if(!$id) $this->error('没有指定视频代号！');
		$opus=D('Opus');
		// 查看作品
		viewOpus($id);
		$lock_hash=md5("refreshVoteCount_lock".$id);
		if(!S($lock_hash)){
			$opus->refreshVoteCount($id);  // 定时刷新
			S($lock_hash,1,300);  // 每隔5分钟刷新一次
		}

		// 作品信息
		$data=$opus->find($id);
		if($data['user_id']!=sess('acc.id')){
			if($data['status']=='1'){
				$this->error('没有找到该作品！');
			}else if($data['status']=='2'){
				$this->error('您不能查看被禁用的作品！');
			}
		}
		
		importP('Attach');
		$data['documents']=Attach::getDoc($id);
		$data['video']=Attach::getVideo($id);
		$this->assign('opus',$data);

		// 用户信息
		$user=D('User');
		$author_info=$user->getUserInfo($data['user_id']);
		$this->assign('author',$author_info);

		// 评论
		importP('Page');
		$count=M('opus_comment')->where(array('force_hidden'=>0,'opus_id'=>$id))->count();
		$page=new Page($count,15);
		$this->assign('pagecfg',array('count'=>$count,'first'=>$page->firstRow+1,'list'=>$page->firstRow+$page->listRows));
		$this->assign('comment_data',$opus->getComment($id,$page->firstRow.','.$page->listRows));
		$this->assign('show',$page->show());

		// 作者的其他作品 
		$other_opus=$opus->where(array('id'=>array('neq',$id),'status'=>0,'user_id'=>array('eq',$author_info['id'])))->select();
		$this->assign('other_opus',$other_opus);

		// 热门作品
		$hot_opus=$opus->where(array('status'=>0))->order('popularity desc')->limit(8)->select();
		$this->assign('hot_opus',$hot_opus);

		// 人气选手
		$hot_user=$user->where(array('forbidden'=>0))->order('popularity desc,id asc')->limit(7)->select();
		$this->assign('hot_user',$hot_user);
		$this->display();
	}

	public function vote(){
		if(!can_vote()) IS_AJAX?$this->ajaxReturn(array(0,"投票时间已截至")):$this->error("投票时间已截至");
		$opus_id=I('opus_id');
		if(!$opus_id) IS_AJAX?$this->ajaxReturn(array(0,"没有指定作品ID！")):$this->error('没有指定作品ID！');
		$opus=D('Opus');
		$opus_vote=M('opus_vote');
		$result=$opus->vote($opus_id);
		if($result[0]==1){
			IS_AJAX?$this->ajaxReturn($result):$this->success($result[1]);
		}else{
			IS_AJAX?$this->ajaxReturn($result):$this->error($result[1]);
		}
	}
	public function comment(){
		if(!$_POST['opus_id']) $this->error("没有指定视频ID！");
		if(strlen($_POST['comments'])<10) $this->error("评论长度必须大于10！");
		if(!sess('acc.id')) $this->error('用户没有登录！');
		$opus=D('Opus');
		$result=$opus->addComment($_POST['opus_id'],$_POST['comments']);
		if($result[0]==1){
			IS_AJAX?$this->ajaxReturn($result):$this->success($result[1]);
		}else{
			IS_AJAX?$this->ajaxReturn($result):$this->error($result[1]);
		}
	}

	public function getOpusInfo($opus_id){
		if(!sess('acc.id')) return;
		$opus=D('Opus');
		$result=$opus->where(array('user_id'=>sess('acc.id'),'id'=>$opus_id))->field('force_top,last_refesh,content',true)->find();
		$result['thumb']=getThumb($result['thumb']);
		if($result){
			$authors=M('opus_author')->where(array('opus_id'=>$opus_id))->field('author')->select();
			$author_str="";
			foreach ($authors as $value) {
				$author_str.=$value['author']."  ";
			}
			$result['author']=$author_str;
			$result['add_time']=date('Y-m-d',$result['add_time']);

			importP('Attach');
			// 作品说明文档
			$result['documents']=Attach::getDoc($opus_id);

			// 视频文件
			
			$video=Attach::getVideo($opus_id);
			$result['video']=$video;

			$this->ajaxReturn($result);
		}else{
			return ;
		}
	}
	/**
     * 获取作品的作者列表
     * @param  [int] $opus_id [作品的ID]
     * @return [string]       [json格式的作者列表]
     * @return [array]        [作者列表数组]
     */
	public function getAuthorList($opus_id){
        $opus=D('Opus');
        $authors=$opus->getAuthorList($opus_id);
        if(IS_AJAX){
            $this->ajaxReturn($authors);
        }else{
            return $authors;
        }
    }

    public function uploadVideo(){
		set_time_limit(0);
		$verifyToken = md5('MC_OPUS_VIDEO_UPLOAD'. $_POST['opus_id'] . $_POST['timestamp']);
		if($verifyToken==$_POST['token']){
			$opus=D('Opus');
			importP('Attach');
		    $attach=new Attach();
		    $result=$attach->uploadVideo($_POST['opus_id']);
	    	echo json_encode($result);
		}else{
			echo json_encode(array(0,"页面认证失败！"));
		}
	}
}
?>
