<?php
class UserCenterAction extends Action{
	function _initialize(){
		if(!sess('acc')){
			XS('LOGIN_FAIL_MESSAGE',"您还未登录，请登录后再进行相关操作");
			$this->redirect('User/retry');
		}
		//设定侧边栏的内容
		$user=D('User');
		$acc=sess('acc');
		$user_data=$user->where(array('id'=>$acc['id']))->find();
		$this->assign('acc',$user_data);

		$msg_unread=M('msg')->where(array('user_id'=>$acc['id'],'has_read'=>0))->count();
		$this->assign('msg_unread',$msg_unread);

		//运行数据读取
        G('beginTime',$GLOBALS['_beginTime']);
        G('viewEndTime');
        $this->assign('loadtime',G('beginTime','viewEndTime'));
        $online_count=M('session')->count();
        $this->assign('online',$online_count);
	}
	public function index(){
		$user=D('User');
		$acc=sess('acc');
		$user_data=$user->where(array('id'=>$acc['id']))->find();
		$opus_data=$user->getOpus($acc['id']);
		$vote_data=$user->getVoteOpus($acc['id'],"8");
		$this->assign('acc',$user_data);
		$this->assign('opus',$opus_data);
		$this->assign('vote',$vote_data);
		$this->display();
	}
	public function message(){
		importP('Page');
		$user=D('User');
		$acc=sess('acc');

		$count=$user->count();
		$page = new Page($count,25);
		$msg=$user->getMessage($acc['id'],$page->firstRow.','.$page->listRows);
        $this->assign('show',$page->show());
		$this->assign('msg',$msg);
		$this->display();
	}
	public function getMsg(){
		$id=I('id');
		$acc=sess('acc');
		if(!$id){echo 0;return;}
		$msg=M('msg');
		$data=$msg->where(array('id'=>$id,'user_id'=>$acc['id']))->find();
		$this->ajaxReturn($data);
	}
	public function readMsg(){
		$id=I('id');
		$acc=sess('acc');
		if(!$id){echo 0;return;}
		$msg=M('msg');
		$msg->where(array('id'=>$id,'user_id'=>$acc['id']))->setField('has_read',1);
		D('User')->refreshMessage($acc['id']);
	}
	public function userInfo(){
		$acc=sess('acc');
		$user=M('user');
		$userInfo=$user->where(array('id'=>$acc['id']))->find();
		$this->assign('data',$userInfo);
		$this->display();
	}
	public function avatar(){
		C('SHOW_PAGE_TRACE',false);
		$acc=sess('acc');
		if(!$acc){echo "用户未登录！";die;}
		$user=M('user');
        if($_FILES){
            importP("Attach");
            $attach=new Attach();
            $msg=$attach->uploadAvatar($acc['id']);
            $this->assign('msg',$msg);
        }
		$userInfo=$user->where(array('id'=>$acc['id']))->field('avatar_large')->find();
		$this->assign('aid',$userInfo['avatar_large']);
		$this->display();

	}
	public function editInfo(){
		$acc=sess('acc');
		$id=$acc['id'];
        $user=M('user');
        $data=$user->create();
        if($data['password']==""){
            unset($data['password']);
        }else{
            $data['password']=passwordHash($acc['uid'],$data['password']);
        }
        $result=$user->where(array('id'=>$id))->save($data);
        $this->success('修改完成！');
	}
	public function OpusManage(){
		$opus=M('opus');
		$acc=sess('acc');
		// 普通作品
		$query=array('status'=>0,'user_id'=>$acc['id']);
		$opus_normal=$opus->where($query)->order('popularity desc')->select();
		$this->assign('opus_normal',$opus_normal);
		// 草稿
		$query['status']=1;
		$opus_draft=$opus->where($query)->order('id asc')->select();
		$this->assign('opus_draft',$opus_draft);
		// 禁用
		$query['status']=2;
		$opus_forbid=$opus->where($query)->order('id desc')->select();
		$this->assign('opus_forbid',$opus_forbid);
		$this->display();
	}

	public function opus_opreation(){
		if(!I('submit')) $this->error('错误的请求方法！');
		if(!I('operate_id')) $this->error('参数错误！');
		$opus_id=I('operate_id',0,'intval');
		switch (I('submit')) {
			case '编辑作品信息':
				$this->redirect('editOpus',array('opus_id'=>$opus_id));break;
			case '管理视频':
				$this->redirect('editVideo',array('opus_id'=>$opus_id));break;
			case '发布':
				$this->redirect('release',array('opus_id'=>$opus_id));break;
			case '取消发布':
				$this->redirect('unrelease',array('opus_id'=>$opus_id));break;
			case '删除':
				$this->redirect('delete',array('opus_id'=>$opus_id));break;
			case '申诉':
				$this->error("暂不提供申诉功能！");
				break;

			default:
				$this->error('您请求的方法不存在！');
				break;
		}
	}
	public function editOpus($opus_id){
		$acc=sess('acc');
		$opus=D('Opus');
		$data=$opus->getOpusData($opus_id);
		if(!$data) $this->error("该视频不存在！");
		$this->assign('data',$data);

		// 作者
		$author=M('opus_author');
		$author_list=$author->where(array('opus_id'=>$opus_id))->select();
		$this->assign("authors",$author_list);

		// 已上传的文档
		importP('Attach');
		$this->assign('att_info',Attach::getDoc($opus_id));
		
		$this->display();
	}
	public function opusthumb(){
		C('SHOW_PAGE_TRACE',false);
		$opus_id=I('opus_id',0,'intval');
		if(!$opus_id){
			echo "没有传入作品ID";die;
		}
		$opus=M('opus');
        if($_FILES){
            importP("Attach");
            $attach=new Attach();
            $msg=$attach->uploadOpusThumb($opus_id);
            $this->assign('msg',$msg);
        }
		$opusInfo=$opus->where(array('id'=>$opus_id))->field('thumb')->find();
		$this->assign('aid',$opusInfo['thumb']);
		$this->display();
	}
	public function editVideo($opus_id){
		importP('Attach');
		$has_uploaded=Attach::getVideo($opus_id);
		$this->assign('old_video',$has_uploaded);
		$this->display();
	}

	
	public function release($opus_id){
		if(!can_upload()) $this->error("不在上传时间内,无法发布！");
		$opus=D('Opus');
		if(!$opus->auth($opus_id,sess('acc.id')))
			$this->error('您不能修改其他人的作品！<br>');
		$opus_data=$opus->find($opus_id);
		if(!$opus_data) $this->error('没有找到这个作品！<br>');
		$err_msg="";
		if($opus_data['init']==1){
			$err_msg.="请填写作品基本信息<br>";
		}
		if(!$opus_data['overview']){
			$err_msg.="作品介绍不完整！请重新填写<br>";
		}
		importP('Attach');
		$docinfo=Attach::getDoc($opus_id);
		if(!$docinfo){
			$err_msg.="必须要上传作品文档后才能发布！<br>";
		}
		$videoinfo=Attach::getVideo($opus_id);
		if(!$videoinfo){
			$err_msg.="必须要上传视频后才能发布！<br>";
		}
		if(strlen($err_msg)){
			$this->error($err_msg);
		}else{
			$opus->where(array('id'=>$opus_id))->setField('status',0);
			$this->success('发布成功！');
		}
	}
	public function unrelease($opus_id){
		if(!can_upload()) $this->error("不在上传时间内,无法取消发布！");
		$opus=D('Opus');
		if(!$opus->auth($opus_id,sess('acc.id')))
			$this->error('您不能修改其他人的作品！<br>');
		$opus_data=$opus->find($opus_id);
		if(!$opus_data) $this->error('没有找到这个作品！<br>');
		$opus->where(array('id'=>$opus_id))->setField('status',1);
		$this->success('取消发布成功！');
	}

	public function delete($opus_id){
		$opus=D('Opus');
		if($opus->auth($opus_id,sess('acc.id'))){
			importP('Attach');
			Attach::deleteFile($opus_id);  // 删除所有跟作品相关的文件
			$opus_view=M('opus_view');
			$opus_view->where(array("opus_id"=>$opus_id))->delete();
			$result=$opus->where(array("id"=>$opus_id))->delete();
			if($result)
				$this->success("删除成功！");
			else
				$this->error("删除失败");
		}else{
			$this->error("您不是该作品的所有者，不能执行删除操作！");
		}
	}

	/**
	 * 上传说明图像类
	 * @return [type] [description]
	 */
	public function upload_pic(){
	    //获取存储目录
	    if ( isset( $_GET[ 'fetch' ] ) ) {
	        header( 'Content-Type: text/javascript' );
	        echo 'updateSavePath('. json_encode(array('作品文档图片目录')) .');';
	        return;
	    }
	    importP('Attach');
	    $attach=new Attach();
	    $result=$attach->uploadOpusImage();
	    if(is_array($result)){
	    	$title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
	    	$return['url']=substr($result['savepath'].$result['savename'], 1);
	    	$return['title']=$title;
	    	$return['original']=$result['name'];
	    	$return['state']='SUCCESS';
	    	echo json_encode($return);
	    }else{
	    	$return['state']=$result;
	    	echo json_encode($return);
	    }
    }
    public function getOpusImage(){
        $action = htmlspecialchars( $_POST[ "action" ] );
        if ( $action == "get" ) {
            importP('Attach');
            $attach=new Attach();
            $data=$attach->getOpusImage();
            $str="";
            foreach ($data as $key => $value) {
                $str.=$value['file_location']."ue_separate_ue";
            }
            echo $str;
        }
    }


	public function checkOpusName_ajax($opus_id,$new_name){
		if(!$new_name) $this->ajaxReturn(array(0,"作品名称不能为空！"));
		if($new_name=="未命名作品") $this->ajaxReturn(array(0,"作品名称不能为“未命名作品”！"));
		$opus=M('opus');
		$result=$opus->where(array('id'=>array('neq',$opus_id),'opus_name'=>$new_name))->count();
		if($result=="0") $this->ajaxReturn(array(1,"该名称可以使用"));
		else $this->ajaxReturn(array(0,"该名称已被占用"));
	}
	private function checkOpusName($opus_id,$new_name){
		if(!$new_name) return(array(0,"作品名称不能为空！"));
		if($new_name=="未命名作品") return(array(0,"作品名称不能为“未命名作品”！"));
		$opus=M('opus');
		$result=$opus->where(array('id'=>array('neq',$opus_id),'opus_name'=>$new_name))->count();
		if($result=="0") return(array(1,"作品名称可以使用"));
		else return(array(0,"作品名称已被占用"));
	}
	public function addOpus(){
		if(!can_upload()) $this->error("不在上传时间内,无法新建作品！");
		$acc=sess('acc');
		$opus=D('Opus');
		$id=$opus->createDraft($acc['id']);
		$data=$opus->getOpusData($id);
		$this->assign('data',$data);

		$user=M('user');
		$this->assign('user_data',$user->find($acc['id']));
		$this->display();
	}
	
	public function editOpus_handle(){
		
		$opus=D('Opus');
		$id=I('id',0,'intval');

		// 错误验证阶段
		if(!$id) throw_exception("作品ID不正确！");
		$opus_data=$opus->find($id);
		if(!$opus_data)
			throw_exception("该作品不存在！");
		if($opus_data['user_id']!=sess('acc.id'))
			$this->error("您不能修改其他人的作品！");
		$name_check=$this->checkOpusName($id,$_POST['opus_name']);
		if($name_check[0]==0){
			$this->error($name_check[1]);
		}
		// 上传作品说明文档
		if($_FILES['document']['name']){
            importP("Attach");
            $attach=new Attach();
            $result=$attach->uploadOpusDoc($id);
            if(!$result[0]){
            	$this->error("视频说明文档上传失败！");
            }
        }

		// 对数据做基本处理
		$authors=array();
		foreach ($_POST['author_name'] as $key => $value) {
			if(!trim($value)) continue;
			else $authors[]=array(
				'opus_id' => $id,
				'author'  => trim($value),
				'sex'     => intval($_POST['sex'][$key]),
				'idcard'  => trim($_POST['idcard'][$key]),
				'belong'  => trim($_POST['author_belong'][$key]),
				'phone'   => trim($_POST['phone'][$key]),
				'email'   => trim($_POST['email'][$key])
				);
		}
		unset($_POST['author_name'],$_POST['idcard'],$_POST['sex'],$_POST['author_belong']);
		unset($_POST['phone'],$_POST['email'],$_POST['id']);
		// 更改数据
		$result2=$opus->updateOpusInfo($id,$_POST);
		$result3=$opus->updateOpusAuthor($id,$authors);
		//当两者同时错误时
		if(!($result2[0] || $result3[0])){
			$this->error("视频信息保存失败！");
		}else{
			if($_POST['FLAG_ADD_OPUS']){
				$this->redirect('editVideo',array('opus_id'=>$id));
			}else{
				$this->success("信息修改成功！",U('OpusManage'));
			}
		}
	}






	public function videoUploadFrame(){
		C('SHOW_PAGE_TRACE',false);
		$opus_id=I('opus_id');
		if(!$opus_id) {
			echo "没有指定视频ID!";die;
		}
		$this->display();
	}
	public function videoUploadHandle(){
		importP('JqueryUpload');
		$options=array(
			"max_number_of_files"=> "/(\.|\/)(gif|jpe?g|png|mkv|mp4|flv|wmv)$",
			"upload_dir" => "./Upload/opus/",
			);
		$upload_handler = new UploadHandler();
	}
	public function opusVote(){
		$user=D('User');
		$vote_data=$user->getVoteOpus(sess('acc.id'));
		$count=count($vote_data);
		$this->assign('count',$count);
		$this->assign('vote_data',$vote_data);
		$this->display();
	}
	public function OpusComment(){
		$user=D('User');
		$data=$user->getCommentByUserID(1,"50");
		import('ORG.Util.String');
		foreach ($data as $key => $value) {
			$data[$key]['message']=String::msubstr($value['message'], 0,80);
		}
		$this->assign('comment',$data);
		$this->display();
	}
}