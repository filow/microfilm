<?php 
class OpusModel extends Model{
	public function createDraft($userid){
		//如果存在一个还未初始化的草稿，则直接读取这个
		$has_draft=$this->where(array('user_id'=>$userid,'status'=>1,'init'=>1))->field('id')->find();
		if($has_draft) return $has_draft['id'];

		$data=array(
			'user_id'  => $userid,
			'opus_name'=> "未命名作品",
			'add_time' => time(),
			'status'   => 1,
			'init'	   => 1,
			);
		$id=$this->add($data);
		$acc=sess('acc');
		$author_data=array(
			'opus_id' => $id,
			'author' => $acc['nickname'],
			'sex' => $acc['sex'],
			'phone' => $acc['phone'],
			'email' => $acc['email']
			);
		M('opus_author')->add($author_data);
		return $id;
	}
	public function getOpusData($opus_id){
		return $this->where(array('id'=>$opus_id))->find();
	}
	public function getRankList(){
		$data=S('OpusRankList');
		if(APP_DEBUG || !$data){
			unset($data);
			$db=M();
			$opus=C('DB_PREFIX')."opus";
			$user=C('DB_PREFIX')."user";
			$sql="select opus.id,opus.opus_name,opus.vote_count,opus.thumb_mini,opus.overview,opus.popularity,user.nickname,user.uid from ".
			     "{$opus} as opus,{$user} as user ".
			     "where opus.`status`=0 and opus.user_id=user.id ".
			     "order by opus.`popularity` desc,opus.`vote_count` desc,opus.`add_time` desc ".
			     "limit 7";
			$data=$db->query($sql);
			S('OpusRankList',$data);
		}
		return $data;
	}
	public function vote($opus_id,$user_id=null){
		$opus_vote=M('opus_vote');
		
		if(getVoteStatus($opus_id)){
			return array(0,"您已经给这个作品投过票了");
		}else{
			$vote=array(
				"opus_id" => $opus_id,
				"user_ip" => getClientIP(),
				"date"    => time()
			);
			if($opus_vote->add($vote)){
				$this->refreshVoteCount($opus_id);
				return array(1,"投票成功");
			}else{
				return array(0,"投票失败");
			}
		}
			
		$opus=M('opus');
		$count=$opus_vote->where(array('opus_id'=>$opus_id))->count();
		$opus->where(array('id'=>$opus_id))->setField('vote_count',$count);
		S('random_video_cache',null);
	}
	public function refreshVoteCount($opus_id){
		$opus_vote=M('opus_vote');
		$opus=M('opus');
		$count=$opus_vote->where(array('opus_id'=>$opus_id))->count();
		$opus->where(array('id'=>$opus_id))->setField('vote_count',$count);
		$this->refreshOpusPop($opus_id);
	}
	public function refreshOpusPop($opus_id){
		$opus=M('opus');
		$data=$opus->field("content",true)->find($opus_id);
		/**
		 * 内容概览评分：最高128*0.1=12.8分，基本作为初始分数
		 * 投票评分:最高200分
		 * 评论得分：最高200分
		 * 查看得分：最高120分
		 * 权重得分:权重×10
		 */
		$rank=strlen($data["overview"])*0.1+
			   $this->lim($data['vote_count']*0.15,200)+
			   $this->lim($data['comment_count']*0.25,200)+
			   $this->lim($data['view_count']*0.05,120)+
			   $this->lim($data['force_top']*10,90);
		$rank=intval($rank);
		$opus->where(array('id'=>$opus_id))->setField('popularity',$rank);
		$this->refreshUserPop($data['user_id']);
	}
	public function refreshUserPop($user_id){
		$opus=M('opus');
		$user=M('user');
		$dataset=$opus->where(array('user_id'=>$user_id))->field('popularity')->select();
		$count=0;
		foreach ($dataset as $value) {
			$count+=$value['popularity'];
		}
		$user->where(array('id'=>$user_id))->setField('popularity',$count);
	}
	private function lim($number,$max){
		if($number>$max) return $max;
		else return $number;
	}

	public function addComment($opus_id,$contents){
		$data=array(
			'opus_id' => intval($opus_id),
			'user_id' => intval(sess('acc.id')),
			'message' => substr(htmlspecialchars($contents),0,2000),
			'date'	  => time(),
			);
		$query=array('user_id' => intval(sess('acc.id')),);
		$opus_cmt=M('opus_comment');
		$lastcmt=$opus_cmt->where($query)->order('date desc')->find();
		if($lastcmt && (time()-$lastcmt['date'])<300){
			return array(0,"两次评论时间间隔不得少于5分钟！");
		}else{
			if($opus_cmt->add($data)){
				$this->refreshCommentCount($opus_id);
				return array(1,"评论发表成功！");
			}else{
				return array(0,"评论发表失败！");
			}
		}
	}

	public function refreshCommentCount($opus_id){
		$opus_comment=M('opus_comment');
		$opus=M('opus');
		$count=$opus_comment->where(array('opus_id'=>$opus_id,'force_hide'=>0))->count();
		$opus->where(array('id'=>$opus_id))->setField('comment_count',$count);
		$this->refreshOpusPop($opus_id);
	}
	public function getComment($opus_id,$limit="0,30"){
		$cmt=CF('DB_PREFIX')."opus_comment";
		$usr=Cf('DB_PREFIX')."user";
		$sql="select cmt.*,usr.uid,usr.nickname,usr.avatar_large ".
			 "from {$cmt} cmt left join {$usr} usr ".
			 "on cmt.user_id = usr.id ".
			 "where opus_id={$opus_id} and force_hide=0 ".
			 "order by date desc limit {$limit}";
		return Model::query($sql);
	}

	public function updateOpusInfo($opus_id,$data){
		import('ORG.Util.Input');
		$new=array(
			'opus_name' => htmlspecialchars(trim($data['opus_name'])),
			'content'   => trim($this->simpleFilter($data['intro'])),
			'add_time'  => time(),
			'overview'  => trim(Input::deleteHtmlTags($data['intro'])),
			'init'      => 0,
			);
		if($this->where(array('id'=>$opus_id))->save($new)){
			return array(1,"作品信息修改成功！");
		}else{
			return array(0,"作品信息修改失败！");
		}
	}
	public function updateOpusAuthor($opus_id,$author_list){
		$opus_author=M('opus_author');
		$opus_author->where(array('opus_id'=>$opus_id))->delete();
		if($opus_author->addAll($author_list)){
			return array(1,"作者信息添加成功！");
		}else{
			return array(0,"作者信息添加失败！");
		}
	}
	private function simpleFilter($text){
        $text =  trim($text);
        //完全过滤注释
        $text = preg_replace('/<!--?.*-->/','',$text);
        //完全过滤动态代码
        $text =  preg_replace('/<\?|\?'.'>/','',$text);
        //完全过滤js
        $text = preg_replace('/<script?.*\/script>/','',$text);

        $text =  str_replace('[','&#091;',$text);
        $text = str_replace(']','&#093;',$text);
        $text =  str_replace('|','&#124;',$text);
        //过滤危险的属性，如：过滤on事件lang js
        while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1].$mat[3],$text);
        }
        return $text;
    }

    public function auth($opus_id,$user_id){
    	$result=$this->where(array('id'=>$opus_id,'user_id'=>$user_id))->find();
    	return $result?true:false;
    }

    public function getAuthorList($opus_id){
        $authors=M('opus_author')->where(array('opus_id'=>$opus_id))->field('author,sex,belong')->select();
        if(!$authors) return false;
        foreach ($authors as $key => $value) {
            if($value['sex']==0) $authors[$key]['sex']="男";
            else if($value['sex']==1) $authors[$key]['sex']="女";
            else $authors[$key]['sex']="";
        }
            return $authors;
    }
}