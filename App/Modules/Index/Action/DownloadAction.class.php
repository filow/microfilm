<?php
class DownloadAction extends Action{
	public function _empty($id){
		$secret=$_GET['secret'];
		if(!$secret){header("HTTP/1.1 404 Not Found");exit(0);}
		$secret=addslashes($secret);
		$attach=M('attach');
		$info=$attach->where(array('access_key'=>$secret))->find();
		if(!$info){header("HTTP/1.1 404 Not Found");exit(0);}
		import('ORG.Net.Http');
		Http::download('.'.$info['file_location'],$info['filename'],'',3600);
	}
}
?>