<?php
class Attach{
// array(1) {  
// [0] => array(8) {   
//  ["name"] => string(38) "2013-12-18 16:45:06çš„å±å¹•æˆªå›¾.png" 
//  ["type"] => string(24) "application/octet-stream"   
//  ["size"] => int(74869)    
//  ["key"] => string(6) "upfile"    
//  ["extension"] => string(3) "png"    
//  ["savepath"] => string(20) "./Upload/notify_img/"    
//  ["savename"] => string(24) "201312/52b992137045f.png"    
//  ["hash"] => string(32) "71384863aabdb1ecc966663da1e5495e"  }}
	public function uploadAvatar($userid){
        import("ORG.Net.UploadFile");
        $config =   array(
            'maxSize'           =>  3*1024*1024,    // 上传文件的最大值
            'supportMulti'      =>  false,    // 是否支持多文件上传
            'allowExts'         =>  array('jpeg','png','jpg'),    // 允许上传的文件后缀 留空不作后缀检查
            'autoSub'           =>  true,// 启用子目录保存文件
            'subType'           =>  'custom',// 子目录创建方式
            'subDir'            =>  'imgtmp/', // 子目录名称 subType为custom方式后有效
            'savePath'          =>  './Upload/',// 上传文件保存路径
        );
        $upload = new UploadFile($config);
        // 上传文件
        if($upload->upload()){
        	import("ORG.Util.Image");
        	$attach=M('attach');
        	$user=M('user');
        	// 获取文件信息
        	$info=$upload->getUploadFileInfo();
		    $fileInfo=$info[0];
		    $path=$fileInfo['savepath'].$fileInfo['savename'];
		    $thumb_name=explode('/', $fileInfo['savename']);
		    // 获得第一个头像的缩略图
		    $thumb1=Image::thumb2($path,"./Upload/thumb/".$thumb_name[1],'',64,64,false);
		    // 插入附件表
		    $attach_info=array(
		    	'filename'=>$fileInfo['name'],
		    	'access_key'=>md5($thumb1),
		    	'time'=>time(),
		    	'file_location'=>substr($thumb1, 1),
		    	'is_image'=>1
		    	);
		    $attach_id1=$attach->add($attach_info);
		    if($attach_id1){
		    	// 删除原来的小头像
		    	$old_avatar_id=$user->field('avatar')->find($userid);
		    	$old_avatar_file=$attach->find($old_avatar_id['avatar']);
		    	$attach->delete($old_avatar_id['avatar']);
		    	unlink(".".$old_avatar_file['file_location']);
		    	$user->where(array('id'=>$userid))->setField('avatar',$attach_id1);
		    }else{
	        	return array(0,"无法将文件信息插入附件表");
		    }
		    $thumb2=Image::thumb2($path,"./Upload/thumb_large/".$thumb_name[1],'',300,300,false);
		    $attach_info['access_key']=md5($thumb2);
		    $attach_info['file_location']=substr($thumb2, 1);
		    $attach_id2=$attach->add($attach_info);
		    if($attach_id2){
		    	// 删除原来的小头像
		    	$old_avatar_id=$user->field('avatar_large')->find($userid);
		    	$old_avatar_file=$attach->find($old_avatar_id['avatar_large']);
		    	unlink(".".$old_avatar_file['file_location']);
		    	$attach->delete($old_avatar_id['avatar_large']);
		    	$user->where(array('id'=>$userid))->setField('avatar_large',$attach_id2);
		    }else{
	        	return array(0,"无法将文件信息插入附件表");
		    }
		    unlink($path);
		    return array(1,"头像更改成功");
        }else{
        	return array(0,$upload->getErrorMsg());
        }
	}

	public function uploadNotifyImage(){
        import("ORG.Net.UploadFile");
        $config =   array(
            'maxSize'           =>  5*1024*1024,    // 上传文件的最大值
            'allowExts'         =>  array('jpeg','png','jpg','gif','webp'),    // 允许上传的文件后缀 留空不作后缀检查
            'autoSub'           =>  true,// 启用子目录保存文件
            'subType'           =>  'date',// 子目录创建方式
            'dateFormat'        =>  'Ym',
            'savePath'          =>  './Upload/notify_img/',// 上传文件保存路径
        );
        $upload = new UploadFile($config);
        if($upload->upload()){
        	$info=$upload->getUploadFileInfo();
        	$fileInfo=$info[0];
        	$notify_attach=M('notification_attach');
    		$data['filename']=$fileInfo['name'];
    		$data['file_location']=substr($fileInfo['savepath'].$fileInfo['savename'], 1);
    		$data['admin_id']=$_SESSION['id'];
    		$data['is_image']=1;
    		$notify_attach->add($data);
        	return $fileInfo;
        }else{
        	return $upload->getErrorMsg();
        }
	}
	public function getNotifyImage(){
		$notify_attach=M('notification_attach');
		$data=$notify_attach->where(array('admin_id'=>$_SESSION['id'],'is_image'=>1))->order('id desc')->select();
		return $data;
	}

	public function uploadNotifyFile(){
        import("ORG.Net.UploadFile");
        $config =   array(
            'maxSize'           =>  50*1024*1024,    // 上传文件的最大值
            'autoSub'           =>  true,// 启用子目录保存文件
            'subType'           =>  'date',// 子目录创建方式
            'dateFormat'        =>  'Ym',
            'savePath'          =>  './Upload/notify_file/',// 上传文件保存路径
        );
        $upload = new UploadFile($config);
        if($upload->upload()){
        	$info=$upload->getUploadFileInfo();
        	$fileInfo=$info[0];
        	$notify_attach=M('notification_attach');
    		$data['filename']=$fileInfo['name'];
    		$data['file_location']=substr($fileInfo['savepath'].$fileInfo['savename'], 1);
    		$data['admin_id']=$_SESSION['id'];
    		$data['is_image']=0;
    		$notify_attach->add($data);
        	return $fileInfo;
        }else{
        	return $upload->getErrorMsg();
        }
	}

    public function deleteAttach($id){
        
    }

    public function uploadOpusDoc($opus_id){
        // 文档只允许同时存在一份，所以删除原有的所有关于这个作品的文档
        $att=M('attach');
        $ops_att=M('opus_attach');
        $att_to_delete=$ops_att->where(array('opus_id'=>$opus_id,'type'=>"D"))->select();
        foreach ($att_to_delete as $value) {
            $fileinfo=$att->find($value['attach_id']);
            unlink(".".$fileinfo['file_location']);
            $att->delete($value['attach_id']);
            $ops_att->where(array('opus_id'=>$opus_id,'type'=>"D"))->delete();
        }

        import("ORG.Net.UploadFile");
        $config =   array(
            'maxSize'           =>  20*1024*1024,    // 上传文件的最大值
            'supportMulti'      =>  false,    // 是否支持多文件上传
            'allowExts'         =>  array('doc'),    // 允许上传的文件后缀 留空不作后缀检查
            'autoSub'           =>  true,// 启用子目录保存文件
            'subType'           =>  'custom',// 子目录创建方式
            'subDir'            =>  'OpusDoc/', // 子目录名称 subType为custom方式后有效
            'savePath'          =>  './Upload/',// 上传文件保存路径
        );
        $upload=new UploadFile($config);
        if($upload->upload()){
            $info=$upload->getUploadFileInfo();
            $info=$info[0];
            $attach_info=array(
                'filename' =>$info['name'],
                'access_key' =>$info['hash'],
                'time'   => time(),
                'file_location' => substr($info['savepath'].$info['savename'],1),
                'is_image' => 0,
            );
            if($att_id=$att->add($attach_info)){
                $ops_info=array(
                    'user_id'  => sess('acc.id'),
                    'opus_id'  => $opus_id,
                    'attach_id' => $att_id,
                    'type' => 'D'
                    );
                if($ops_att->add($ops_info)){
                    return array(1,"文件保存成功！");
                }else{
                    unlink($info['savepath'].$info['savename']);
                    $att->delete($att_id);

                    return array(0,"文件信息保存失败！");
                }
            }else{
                unlink($info['savepath'].$info['savename']);
                return array(0,"文件信息保存错误！");
            }
        }else{
            return array(0,$upload->getErrorMsg());
        }
    }


    public function uploadOpusImage(){
        import("ORG.Net.UploadFile");
        $config =   array(
            'maxSize'           =>  5*1024*1024,    // 上传文件的最大值
            'allowExts'         =>  array('jpeg','png','jpg','gif','webp'),    // 允许上传的文件后缀 留空不作后缀检查
            'savePath'          =>  './Upload/opus_content_img/',// 上传文件保存路径
        );
        $upload = new UploadFile($config);
        if($upload->upload()){
            $info=$upload->getUploadFileInfo();
            $fileInfo=$info[0];
            $att=M('attach');
            $ops_att=M('opus_attach');
            $attach_data=array(
                'filename' =>$fileInfo['name'],
                'file_location' => substr($fileInfo['savepath'].$fileInfo['savename'], 1),
                'access_key' => $fileInfo['hash'],
                'time'=>time(),
                'is_image' => 0,
                );
            $att_id=$att->add($attach_data);
            $img_info=array(
                'user_id'  => sess('acc.id'),
                'attach_id' => $att_id,
                'type' => 'T'
                );
            $ops_att->add($img_info);
            return $fileInfo;
        }else{
            return $upload->getErrorMsg();
        }
    }

    public function getOpusImage(){
        $att=M('attach');
        $ops_att=M('opus_attach');
        $att_list=$ops_att->where(array('user_id'=>sess("acc.id"),'type'=>"T"))->select();
        $result=array();
        foreach ($att_list as $key => $value) {
            $result[]=$att->find($value['attach_id']);
        }
        return $result;
    }

    public function uploadVideo($opus_id){
         // 视频只允许同时存在一份，所以删除原有的所有关于这个作品的视频
        $att=M('attach');
        $ops_att=M('opus_attach');
        $att_to_delete=$ops_att->where(array('opus_id'=>$opus_id,'type'=>"V"))->select();
        foreach ($att_to_delete as $value) {
            $fileinfo=$att->find($value['attach_id']);
            unlink(".".$fileinfo['file_location']);
            $att->delete($value['attach_id']);
            $ops_att->where(array('opus_id'=>$opus_id,'type'=>"V"))->delete();
        }

        import("ORG.Net.UploadFile");
        $config =   array(
            'maxSize'           =>  810*1024*1024,    // 上传文件的最大值
            'supportMulti'      =>  false,    // 是否支持多文件上传
            'allowExts'         =>  array('mp4','flv','mov'),    // 允许上传的文件后缀 留空不作后缀检查
            'autoSub'           =>  true,// 启用子目录保存文件
            'subType'           =>  'custom',// 子目录创建方式
            'subDir'            =>  'OpusVideo/', // 子目录名称 subType为custom方式后有效
            'savePath'          =>  './Upload/',// 上传文件保存路径
        );
        $upload=new UploadFile($config);
        if($upload->upload()){
            $info=$upload->getUploadFileInfo();
            $info=$info[0];
            $attach_info=array(
                'filename' =>$info['name'],
                'access_key' =>$info['hash'],
                'time'   => time(),
                'file_location' => substr($info['savepath'].$info['savename'],1),
                'is_image' => 0,
            );
            if($att_id=$att->add($attach_info)){
                $ops_info=array(
                    'user_id'  => sess('acc.id'),
                    'opus_id'  => $opus_id,
                    'attach_id' => $att_id,
                    'type' => 'V'
                    );
                if($ops_att->add($ops_info)){
                    return array(1,"文件保存成功！");
                }else{
                    unlink($info['savepath'].$info['savename']);
                    $att->delete($att_id);

                    return array(0,"文件信息保存失败！");
                }
            }else{
                unlink($info['savepath'].$info['savename']);
                return array(0,"文件信息保存错误！");
            }
        }else{
            return array(0,$upload->getErrorMsg());
        }
    }
    public static function getVideo($opus_id){
        $att=M('attach');
        $ops_att=M('opus_attach');
        $att_list=$ops_att->where(array('opus_id'=>$opus_id,'type'=>"V"))->find();
        if(!$att_list) return false;
        $result=$att->find($att_list['attach_id']);
        if($result)
            return $result;
        else
            return false;
    }
    public static function getDoc($opus_id){
        $att=M('attach');
        $ops_att=M('opus_attach');
        $att_list=$ops_att->where(array('opus_id'=>$opus_id,'type'=>"D"))->find();
        if($att_list)
            return $att->find($att_list['attach_id']);
        else
            return false;
    }



    public function uploadOpusThumb($opus_id){
        import("ORG.Net.UploadFile");
        $config =   array(
            'maxSize'           =>  3*1024*1024,    // 上传文件的最大值
            'supportMulti'      =>  false,    // 是否支持多文件上传
            'allowExts'         =>  array('jpeg','png','jpg'),    // 允许上传的文件后缀 留空不作后缀检查
            'autoSub'           =>  true,// 启用子目录保存文件
            'subType'           =>  'custom',// 子目录创建方式
            'subDir'            =>  'imgtmp/', // 子目录名称 subType为custom方式后有效
            'savePath'          =>  './Upload/',// 上传文件保存路径
        );
        $upload = new UploadFile($config);
        // 上传文件
        if($upload->upload()){
            import("ORG.Util.Image");
            $attach=M('attach');
            $opus=M('opus');
            // 获取文件信息
            $info=$upload->getUploadFileInfo();
            $fileInfo=$info[0];
            $path=$fileInfo['savepath'].$fileInfo['savename'];
            $thumb_name=explode('/', $fileInfo['savename']);
            // 获得第一个缩略图
            $thumb1=Image::thumb2($path,"./Upload/thumb_large/".$thumb_name[1],'',400,320,false);
            // 插入附件表
            $attach_info=array(
                'filename'=>$fileInfo['name'],
                'access_key'=>md5($thumb1),
                'time'=>time(),
                'file_location'=>substr($thumb1, 1),
                'is_image'=>1
                );
            $attach_id1=$attach->add($attach_info);
            if($attach_id1){
                // 删除原来的缩略图
                $old_file_id=$opus->field('thumb')->find($opus_id);
                $old_file=$attach->find($old_file_id['thumb']);
                $attach->delete($old_file_id['thumb']);
                unlink(".".$old_file['file_location']);
                $opus->where(array('id'=>$opus_id))->setField('thumb',$attach_id1);
            }else{
                return array(0,"无法将文件信息插入附件表");
            }
            $thumb2=Image::thumb2($path,"./Upload/thumb/".$thumb_name[1],'',200,160,false);
            $attach_info['access_key']=md5($thumb2);
            $attach_info['file_location']=substr($thumb2, 1);
            $attach_id2=$attach->add($attach_info);
            if($attach_id2){
                // 删除原来的小缩略图
                $old_file_id=$opus->field('thumb_mini')->find($opus_id);
                $old_file=$attach->find($old_file_id['thumb_mini']);
                unlink(".".$old_file['file_location']);
                $attach->delete($old_file_id['thumb_mini']);
                $opus->where(array('id'=>$opus_id))->setField('thumb_mini',$attach_id2);
            }else{
                return array(0,"无法将文件信息插入附件表");
            }
            unlink($path);
            return array(1,"缩略图更改成功");
        }else{
            return array(0,$upload->getErrorMsg());
        }
    }

    public static function deleteFile($opus_id){
        $att=M('attach');
        $opus_att=M('opus_attach');
        $att_list=$opus_att->where(array('opus_id'=>$opus_id))->select();
        // 必须先删除opus_attach才能删除附件表中的字段
        $opus_att->where(array('opus_id'=>$opus_id))->delete();
        foreach ($att_list as $key => $value) {
            $att_info=$att->find($value['attach_id']);
            if($att_info){
                unlink(".".$att_info['file_location']);
                $att->where(array("id"=>$att_info['id']))->delete();
            }
            unset($att_info);
        }
    }

}