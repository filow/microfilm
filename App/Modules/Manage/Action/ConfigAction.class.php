<?php
class ConfigAction extends CommonAction {
    public function index(){
    	$config=M('config');
    	$config_data['global']=$config->where(array('range'=>0))->select();
    	$config_data['index']=$config->where(array('range'=>1))->select();
    	$config_data['manage']=$config->where(array('range'=>2))->select();
        $data=array();
        foreach ($config_data as $sub_key => $sub_config) {
            foreach ($sub_config as $key => $value) {
                $data[$sub_key][$value['var']]=$value;
            }
        }
        $this->assign('global',$data['global']);
        $this->assign('index',$data['index']);
    	$this->assign('manage',$data['manage']);
        $this->assign('upload_url',U('setConfig'));
    	$this->display();
    }
     public function show_config(){
        $config=M('config');
        $config_data['global']=$config->where(array('range'=>0))->select();
        $config_data['index']=$config->where(array('range'=>1))->select();
        $config_data['manage']=$config->where(array('range'=>2))->select();
        $data=array();
        foreach ($config_data as $sub_key => $sub_config) {
            foreach ($sub_config as $key => $value) {
                $data[$sub_key][$value['var']]=$value;
            }
        }
        $this->assign('global',$data['global']);
        $this->assign('index',$data['index']);
        $this->assign('manage',$data['manage']);
        $this->assign('upload_url',U('setConfig'));
        $this->display();
    }
    public function setConfig(){
        if(!CPM('edit_config')) $this->error("用户权限不足");
    	$config=M('config');
    	$id=I('id');
        $value['value']=I('value');
        if(preg_match('/^\d{4}-\d{2}-\d{2}/', $value['value'])){
            $value['value']=strtotime($value['value']);
        }
    	if($config->where(array('id'=>$id))->save($value))
    		$this->success('修改成功',U('index'));
    	else
    		$this->error('修改失败');

    }
    public function youkuid(){
        $url="https://openapi.youku.com/v2/oauth2/authorize?";
        $url.="client_id=".CF('CLIENT_ID')."&";
        $url.="response_type=code&";
        $url.="redirect_uri=".CF('REDIRECT_URI')."&";
        $url.="state=";
        $this->assign('url',$url);
        $this->display();
    }
    public function setNewToken(){
        $refresh_token=I('refresh_token');
        if(strlen($refresh_token)!=32){
            echo "-1";return;
        }
        $config=M('config');
        $config->where(array('var'=>'REFRESH_TOKEN'))->setField('value',$refresh_token);
        CF('REFRESH_TOKEN',true);
        echo "1";
    }

}