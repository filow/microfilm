<?php
class SearchAction extends CommonAction {
	public function _empty($name){
		$str=I('q');
		if(!$str) $this->error("请输入关键词！");
		$query_words=explode(" ",trim($str));
		foreach ($query_words as $key => $value) {
			if(!$value) unset($query_words[$key]);
		}
		$count=array();  // 结果数量统计
		$result=array(); // 结果数组


		$opus=M('opus');
		$user=D('User');
		$notify=M('notification');
		// sql请求生成
		$query_str['opus']="";
		$query_str['user']="";
		$query_str['news']="";
		foreach($query_words as $key => $value){
			if($query_str['opus']) $query_str['opus'].="AND ";
			if($query_str['user']) $query_str['user'].="AND ";
			if($query_str['news']) $query_str['news'].="AND ";
			$query_str['opus'].="opus_name like '%".addslashes($value)."%' ";
			$query_str['user'].="(nickname like '%".addslashes($value)."%' OR department like '%".$value."%')";
			$query_str['news'].="(title like '%".addslashes($value)."%' OR content_notag like '%".$value."%')";
		}
		$query_str['opus'].=" AND status=0";
		$query_str['user'].=" AND forbidden=0";
		$query_str['news'].=" AND force_hide=0";
		// 作品
		$result['opus'] = $opus->where($query_str['opus'])->select();
		$count['opus']=count($result['opus']);
		if($result['opus']){
			foreach ($result['opus'] as $key => $value) {
				$userdata=$user->where(array('id'=>$value['user_id']))->find();
				$userdata['department']=$user->getUserDepart($value['user_id']);
				$result['opus'][$key]['userinfo']=$userdata;
				$result['opus'][$key]['opus_name']=$this->setKeyWords($result['opus'][$key]['opus_name'],$query_words);
			}
		}
		

		// 参赛选手
		$result['user'] = $user->where($query_str['user'])->select();
		$count['user']=count($result['user']);
		if($result['user']){
			foreach ($result['user'] as $key => $value) {
				$result['user'][$key]['nickname']=$this->setKeyWords($result['user'][$key]['nickname'],$query_words);
				$result['user'][$key]['department']=$user->getUserDepart($value['id']);
				$result['user'][$key]['department']=$this->setKeyWords($result['user'][$key]['department'],$query_words);
				$opuses=$opus->where(array('user_id'=>$value['id'],'status'=>0))->field('id,opus_name')->select();
				$result['user'][$key]['opus']=$opuses;
			}
		}

		// 新闻公告
		$result['news'] = $notify->where($query_str['news'])->field('content',true)->select();
		$count['news']=count($result['news']);
		if($result['news']){
			foreach ($result['news'] as $key => $value) {
				$result['news'][$key]['title']=$this->setKeyWords($result['news'][$key]['title'],$query_words);
				$result['news'][$key]['content_notag']=mb_substr($this->setKeyWords($result['news'][$key]['content_notag'],$query_words), 0,300);
			}
		}
		$this->assign('key_words',$str);
		$this->assign('result',$result);
		$this->assign('count',$count);
		$this->display('result');
	}
	/**
	 * 设置关键词高亮的字符串处理函数
	 * @param [string] $str      [要高亮的字符串]
	 * @param array  $word_arr [关键词]
	 */
	public function setKeyWords($str,$word_arr=array()){
		// 设置多字节字符内部编码为utf8
		mb_internal_encoding("UTF-8");
		// 创建一个跟字符串长度一致的数组，用0填充
		$map=array_fill(0,mb_strlen($str),0);
		// 遍历关键词数组，将关键词对应的map数组的位置上的数字置为1
		foreach ($word_arr as $value) {
			$pos=-1;
			$pos_count=0;
			$pos_arr=array();
			// 如果找到了这个关键词，就将这个词的位置存入位置数组中（来支持多次出现此关键词的情况）
			while(($pos=mb_strpos($str,$value,$pos+1))!==false && $pos_count<5){
				$pos_arr[]=$pos;
				$pos_count++;
			}
			// 遍历数组，将对应位置置1
			foreach ($pos_arr as $pos_val) {
				if($pos_val!==false){
					$fill=array_fill($pos_val,mb_strlen($value),1);
					$map = array_replace($map,$fill);
				}
			}
			$pos=null;
		}

		// 遍历map数组，加入高亮代码
		$flag=0;
		$position=-1;
		$result="";  // 结果数组
		foreach ($map as $key => $value) {
			if($value==1){
				// 如果第一次出现1,则加上html标签头
				if($flag==0) $result.="<span class=\"keyword\">";
				$flag=1;
			}else{
				// 如果已经到了一个0,但上一个还是1时，加入html标签尾
				if($flag==1){
					$position=$key-1;
					$flag=0;
					$result.="</span>";
				}
			}
			// 将该位置的字符加入结果字符串中
			$result.=mb_substr($str,$key,1);
		}
		return $result;
	}
}
