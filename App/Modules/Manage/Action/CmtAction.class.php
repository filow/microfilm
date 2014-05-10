<?php
class CmtAction extends CommonAction {
    public function index()
    {
        $cmt=D('Cmt');
        importP('Page');
        $count=M('opus_comment')->count();
        $page = new Page($count,25);
        $cmt_data=$cmt->getCmtInfo(null,$page->firstRow.','.$page->listRows);
        import('ORG.Util.String');
        foreach ($cmt_data as $key => $value) {
            $cmt_data[$key]['message']=String::msubstr($value['message'],0,30);
        }
        $this->assign('cmt_data',$cmt_data);
        $this->assign('show',$page->show());
        $this->display();
    }
    public function action_handle(){
        switch ($_POST['type']) {
            case 'view':
                $this->view();
                break;
            case 'hide':
                $this->hide();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                XS('message',array(0,"操作不正确！"));
                $this->redirect('index');
                break;
        }
    }
    public function viewByfilter(){
        $cmt=D('Cmt');
        $data=$cmt->getCmtByFilter(I('opus_id'),I('user_id'));
        if($data){
            $this->assign('cmt_data',$data);
            $this->display('view');

        }else{
            $this->error("传入参数错误！");
        }
    }
    private function view(){
        $cmt=D('Cmt');
        $cmt_data=array();
        foreach ($_POST['select'] as $key => $value) {
            $tmp=$cmt->getCmtInfo($value);
            $cmt_data[]=$tmp[0];
        }
        $this->assign('cmt_data',$cmt_data);
        $this->display('view');
    }
    private function hide(){
        $cwt=M('opus_comment');
        $msg="";
        foreach ($_POST['select'] as $key => $value) {
            $status=$cwt->field('message,force_hide')->find($value);
            if($status['force_hide']==1){
                $cwt->where(array('id'=>$value))->setField('force_hide',0);
                $msg.=msubstr($status['message'],0,15)."取消隐藏<br>";
            }else{
                $cwt->where(array('id'=>$value))->setField('force_hide',1);
                $msg.=msubstr($status['message'],0,15)."已经被隐藏<br>";
            }
        }
        XS('message',array(1,$msg));
        $this->redirect('index');
    }
    private function delete(){
        $cwt=M('opus_comment');
        $msg="";
        foreach ($_POST['select'] as $key => $value){
            $message=$cwt->field('message')->find($value);
            $status=$cwt->where(array('id'=>$value))->delete();
            if($status){
                $msg.=msubstr($message['message'],0,15)."删除成功<br>";
            }else{
                $msg.=msubstr($message['message'],0,15)."删除失败<br>";
            }
        }
        XS('message',array(1,$msg));
        $this->redirect('index');  
    }
}