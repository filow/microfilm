<?php
class PassageAction extends CommonAction {
    public function newPassage(){
    	$this->display();
    }
    public function newPassageHandle(){
        import('ORG.Util.Input');
        if(strlen($_POST['title'])<5) $this->error('标题必须大于5个字符');
        $_POST['content']=$this->simpleFilter($_POST['content']);
        $notify=M('notification');
        $data=$notify->create($_POST);
        $data['content_notag']=Input::deleteHtmlTags($_POST['content']);
        $data['valid_from']=strtotime($_POST['start']);
        $data['date']=time();
        if($notify->add($data)){
            msg(1,"文章发布成功！",'passageList');
        }else{
            $this->error('由于系统存在异常，发布失败！');
        }
    }
    public function passageList()
    {
        $notify=M('notification');
        import('ORG.Util.String');
        importP('Page');
        $count=$notify->count();
        $page = new Page($count,20);
        $list=$notify->order('force_top desc,id desc')->field('content',true)
                ->limit($page->firstRow.','.$page->listRows)->select();
        foreach ($list as $key => $value) {
            $ctime=time();
            $from=$value['valid_from'];
            $class="";
            if($value['force_top']){
                $status="置顶/";
                $class="info ";
            }
            if($value['force_hide']){ 
                $status.="隐藏/";
                $class.="greyline";
            }
            if($ctime<$from){
                $status.="还未发布";
            }else{
                $status.="已发布";
            }
            $list[$key]['status']=$status;
            $list[$key]['class']=$class;

            $list[$key]['content_notag']=String::msubstr($value['content_notag'],0,60);
            $list[$key]['valid_from']=date('y-m-d H:i',$value['valid_from']);
            $list[$key]['date']=$value['date']?date('y-m-d H:i',$value['date']):"";
            $status="";
        }
        $this->assign('show',$page->show());
        $this->assign('notify',$list);
        $this->display();
    }
    public function action_handle(){
        switch ($_GET['type']) {
            case 'edit':
                $this->edit();
                break;
            case 'view':
                $this->view();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'force_top':
                $this->force_top();
                break;
            case 'switch':
                $this->switch_status();
            default:
                msg(0,"操作不正确！",'passageList');
                break;
        }
    }
    private function view(){
        $id=I('select',0,'intval');
        if(!$id) msg(0,"您没有选择通知项！",'passageList');
        $notify=M('notification');
        $data=$notify->field('content_notag',true)->find($id);
        $data['valid_from']=date('y-m-d H:i',$data['valid_from']);
        $data['date']=$data['date']?date('Y-m-d H:i',$data['date']):"无";
        $this->assign('data',$data);
        $this->display('view');
    }
    private function edit(){
        $id=I('select',0,'intval');
        if(!$id) msg(0,"您没有选择通知项！",'passageList');
        $notify=M('notification');
        $data=$notify->find($id);
        $this->assign('data',$data);
        $this->display('edit');
    }
    public function editHandle(){
        import('ORG.Util.Input');
        if(strlen($_POST['title'])<5) $this->error('标题必须大于5个字符');
        $_POST['content']=$this->simpleFilter($_POST['content']);
        $notify=M('notification');
        $data=$notify->create($_POST);
        $data['content_notag']=Input::deleteHtmlTags($_POST['content']);
        $data['valid_from']=strtotime($_POST['start']);
        $data['date']=time();
        if($notify->save($data)){
            msg(1,"文章修改成功！",'passageList');
        }else{
            $this->error('由于系统存在异常，发布失败！');
        }
    }

    private function force_top(){
        $id=I('select',0,'intval');
        if(!$id) msg(0,"您没有选择通知项！",'passageList');
        $notify=M('notification');
        $notify_data=$notify->find($id);
        if($notify_data['force_top']==1){
            $result=$notify->where(array('id'=>$id))->setField('force_top',0);
            if($result) msg(1,"取消置顶成功",'passageList');
            else msg(0,"取消置顶失败",'passageList');
        }else{
            $result=$notify->where(array('id'=>$id))->setField('force_top',1);
            if($result) msg(1,"置顶成功",'passageList');
            else msg(0,"置顶失败",'passageList');
        }
    }
    private function switch_status(){
        $id=I('select',0,'intval');
        if(!$id) msg(0,"您没有选择通知项！",'passageList');
        $notify=M('notification');
        $notify_data=$notify->find($id);
        if($notify_data['force_hide']==1){
            $result=$notify->where(array('id'=>$id))->setField('force_hide',0);
            if($result) msg(1,"取消隐藏成功",'passageList');
            else msg(0,"取消隐藏失败",'passageList');
        }else{
            $result=$notify->where(array('id'=>$id))->setField('force_hide',1);
            if($result) msg(1,"隐藏文章成功",'passageList');
            else msg(0,"隐藏文章失败",'passageList');
        }
    }
    private function delete(){
        $this->error('暂不提供此功能');
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
    public function upload_passage_pic()
    {
	    //获取存储目录
	    if ( isset( $_GET[ 'fetch' ] ) ) {
	        header( 'Content-Type: text/javascript' );
	        echo 'updateSavePath('. json_encode(array('notify_img/')) .');';
	        return;
	    }
	    importP('Attach');
	    $attach=new Attach();
	    $result=$attach->uploadNotifyImage();
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
    public function get_passage_pic(){
        $action = htmlspecialchars( $_POST[ "action" ] );
        if ( $action == "get" ) {
            importP('Attach');
            $attach=new Attach();
            $data=$attach->getNotifyImage();
            $str="";
            foreach ($data as $key => $value) {
                $str.=$value['file_location']."ue_separate_ue";
            }
            echo $str;
        }

    }

    public function upload_passage_file()
    {
        importP('Attach');
        $attach=new Attach();
        $result=$attach->uploadNotifyFile();
        if(is_array($result)){
            $return['url']=substr($result['savepath'].$result['savename'], 1);
            $return['fileType']=".".$result['extension'];
            $return['original']=$result['name'];
            $return['state']='SUCCESS';
            echo json_encode($return);
        }else{
            $return['state']=$result;
            echo json_encode($return);
        }
    }

    public function sendmsg($user_arr)
    {
        $user=M('user');
        $user_list=array();
        $user_list_str="";
        foreach ($user_arr as $key => $value) {
            $userinfo=$user->field('id,nickname')->find($value);
            $user_list[]=$userinfo;
            $user_list_str.=$userinfo['id'].",";
        }
        $this->assign('user_list',$user_list);
        $this->assign('user_list_str',$user_list_str);
        $this->display('Passage/sendmsg');
    }

    public function sendmsg_handle()
    {
        $msg=M('msg');
        $user_list=explode(",",$_POST['userlist']);
        $title=mysql_escape_string(htmlspecialchars($_POST['title']));
        $content=$_POST['message'];
        $sender=mysql_escape_string($_POST['sender']);
        if($title=="") $this->error("必须填写标题！");
        $query=array(
            "title" => $title,
            "content" => $content,
            "time" => time(),
            "from" => $sender
            );
        $count_all=0;
        $count['success']=0;
        $count['fail']=0;
        foreach ($user_list as $key => $value) {
            if($value=="") continue;
            $query['user_id']=$value;
            $count_all++;
            if($msg->add($query)) $count['success']++;
            else $count['fail']++;
        }
        $this->success('共发送了'.$count_all."条信息，其中".$count['success']."条成功，".$count['fail']."条失败",U('User/index'));
    }

}