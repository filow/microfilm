<?php
class JudgeAction extends CommonAction {
	public function OpusList(){
		$judge=D('Judge');
		$opus_list=$judge->getOpusList(session('id'));
    // dump($opus_list); 
		$this->assign("opus_list",$opus_list);
		$this->display();
	}

  public function OpusInfo(){
    $id=I('opus_id');
    $opus=M('opus');
    $judge=D('Judge');
    $remote_Opus=D('Index/Opus');
    $opus_info=$opus->find($id);
    $author_list=$remote_Opus->getAuthorList($id);
    $judge_status=$judge->checkJudgeStatus($id,session('id'));
    $this->assign('status',$judge_status);
    $this->assign("author_list",$author_list);
    $this->assign('opus_info',$opus_info);
    if($this->checkPermission('VIEW_JUDGE')){
      $judge_field=CF('DB_PREFIX')."judge";
      $admin_field=CF('DB_PREFIX')."admin";
      $sql="SELECT judge.*,admin.realname,admin.uid FROM {$judge_field} as judge, {$admin_field} as admin WHERE judge.opus_id={$id} and judge.judger_id=admin.id";
      
      $judge_list=$judge->query($sql);
      $judge_list_count=0;
      $judge_list_average=0;
      foreach ($judge_list as $value) {
        $judge_list_count++;
        $judge_list_average+=$value['rank'];
      }
      $judge_list_average/=$judge_list_count;
      $this->assign("judge_list",$judge_list);
      $this->assign("judge_list_average",$judge_list_average);
      $this->assign("judge_list_count",$judge_list_count);
    }
    $this->display();
  }
  public function submitJudge(){
    $judge=D('Judge');
    $opus_id=intval($_POST['opus_id']);
    if(!$opus_id) $this->error('数据不合法！！');
    $data=array(
      'opus_id' => $opus_id,
      'rank' => intval($_POST['grade']),
      'comment' => htmlspecialchars($_POST['comment']),
      'judger_id' => session('id')
      );
    if(!$data['rank'] || $data['rank']<1 || $data['rank']>100 ){
      $this->error('分数应当在1-100之间！');
    }
    $selector=array('opus_id'=>$opus_id,'judger_id' => session('id'));
    if($judge->checkJudgeStatus($opus_id,$data['judger_id'])){
      $judge->where($selector)->save($data);
    }else{
      $judge->add($data);
    }
    if($judge->checkJudgeStatus($opus_id,$data['judger_id'])){
      $this->success('提交成功！');
    }else{
      $this->error('提交失败！');
    }
  }
}
