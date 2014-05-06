<?php

class JudgeModel extends Model{
  public function getOpusList($admin_id){
    $opus=D('Opus');
    $opus_list=$opus->where(array('status'=>0))->select();
    $judged_opuses=$this->where(array('judger_id'=>$admin_id))->field('opus_id')->select();
    foreach ($opus_list as $key => $value) {
      $opus_list[$key]['username']=$opus->getUserName($value['user_id']);
      $opus_list[$key]['judged']=0;
      foreach ($judged_opuses as $item) {
        if($item['opus_id']==$value['id']) $opus_list[$key]['judged']=1;
      }
    }
  
    return $opus_list;
  }
  public function checkJudgeStatus($opus_id,$admin_id)
  {
    $result=$this->where(array('opus_id'=>$opus_id,'judger_id'=>$admin_id))->find();
    return $result;
  }
}
