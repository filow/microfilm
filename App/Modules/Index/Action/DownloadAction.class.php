<?php
class DownloadAction extends Action{
	public function _empty($id){
		$secret=$_GET['secret'];
		if(!$secret){$this->error("没有传入文件密钥！");}
		$secret=addslashes($secret);
		$attach=M('attach');
		$info=$attach->where(array('access_key'=>$secret))->find();
		if(!$info){$this->error("文件未找到！");}
		import('ORG.Net.Http');
		Http::download('.'.$info['file_location'],$info['filename'],'',3600);
	}
}
?>