<?php
class IndexAction extends CommonAction {
    public function index(){
    	import('ORG.Util.String');
        $notify=M('notification');
        $opus=D('Opus');
        $user=D('User');

        // 新闻公告
        if(APP_DEBUG || !S('notify_data')){
            // 读取新闻公告
            $notify_data=$notify
                        ->where(array('force_hide'=>0,'valid_from'=>array('lt',time())))
                        ->order('force_top desc,date desc,id desc')
                        ->limit(5)->field('content',true)->select();
            foreach ($notify_data as $key => $value) {
                $notify_data[$key]['title']=String::msubstr($value['title'],0,30,'utf-8',false);
            }
            S('notify_data',$notify_data,CF('INDEX_CACHE_LIFTTIME'));
        }else{
            $notify_data=S('notify_data');
        }
    	$this->assign('notify',$notify_data);

        if(APP_DEBUG || !S('intro_data')){
            $intro_list=M('intro')->order('sort')->select();
            $intro_data=array();
            foreach ($intro_list as $key => $value) {
                $url=U('passage/'.$value["passage_id"])."#".$value['anchor'];
                $intro_data[]=array(
                    'url' => $url,
                    'title' => $value['title']
                );
            }
            S('intro_data',$intro_data,CF('INDEX_CACHE_LIFTTIME'));
        }else{
            $intro_data=S('intro_data');
        }
        $this->assign('intro',$intro_data);

        // 轮播区数字显示
        if(!S('SLIDE_CACHE')){
            //截止时间
            $time_left=CF('UPLOAD_DATE_TO')-time();

            if($time_left<0) $time_left=0;
            if($time_left>3600*24){
                $slide['time_left']=intval($time_left/(3600*24))."天";
            }else if($time_left>3600){
                $slide['time_left']=intval($time_left/(3600))."小时";
            }else{
                $slide['time_left']=intval($time_left/(60))."分钟";
            }
            // 用户总数
            $slide['user_count']=M('user')->where(array('forbidden'=>0))->count();
            // 在线总数
            $slide['online_count']=M('session')->count();
            $slide['str']=array(
                "当前系统共有".$slide['user_count']."个用户",
                "本网站是中国大学生计算机设计大赛的参赛作品，不做实际用途",
                "网站共被访问". S('VISIT_COUNT')."次",
                );
            if($slide['time_left']<=0){
                $slide['str'][]="上传已截至，投票/发布作品功能已关闭！";  
            }else{
                $slide['str'][]="距离上传时间截止还有".$slide['time_left'];
            }
            S('SLIDE_CACHE',$slide,CF('INDEX_CACHE_LIFTTIME'));
        }else{
            $slide=S('SLIDE_CACHE');
        }
        
        $this->assign('slide',json_encode($slide['str']));
        $this->assign('slide1',$slide['str'][0]);

        //判断用户登录状态，如果已经登录则读取数据
        if(sess('acc')){
            $user_data=$user->getUserInfo(sess('acc.id'));
            $msg_unread=M('msg')->where(array('user_id'=>sess('acc.id'),'has_read'=>0))->count();
            $opus_count=$opus->where(array('user_id'=>sess('acc.id'),'status'=>0))->count();
            $this->assign('acc',$user_data);
            $this->assign('opus_count',$opus_count);
            $this->assign('msg_unread',$msg_unread);
        }

        /**
         * 两个排行榜
         * 因为有较大的实时性需求，由于网站访问量不大，故不设置缓存
         */
        // 作品排行榜
        $opus_rank=$opus->getRankList();
        if($opus_rank){
            $this->assign('opus_rank_first',$opus_rank[0]);
            unset($opus_rank[0]);
        }
        $this->assign('opus_rank',$opus_rank);
		

        // 用户排行榜
        
        $user_rank=$user->getRankList();
        $this->assign('user_rank_first',$user_rank[0]);
        unset($user_rank[0]);
        $this->assign('user_rank',$user_rank);

        // 示例视频
        $exp_video_cache=S('exp_video_cache');
        if($exp_video_cache){
            $this->assign('exp_video',$exp_video_cache);
        }else{
            $exp_video=M('exp_video');
            $exp=$exp_video->order('id asc')->limit(5)->select();
            $this->assign('exp_video',$exp);
            S('exp_video_cache',$exp,3600*4);//每4小时更新一次
        }
        

        // 作品欣赏
        // 由于orderby rand()非常消耗性能，故每20分钟更新一次
        $random_video=S('random_video_cache');
        if(!$random_video){
            $mc_opus=CF('DB_PREFIX')."opus";
            $mc_user=CF('DB_PREFIX')."user";

            $sql="select opus.*,user.nickname,user.avatar,user.uid,user.department ".
                 "from {$mc_opus} as opus,{$mc_user} as user ".
                 "where opus.status=0 AND opus.user_id=user.id order by rand() limit 4";
            $random_video=M()->query($sql);
            foreach ($random_video as $key => $value) {
                $random_video[$key]['authors']=$opus->getAuthorList($value['id']);
            }
            
            S('random_video_cache',$random_video,1200);// 缓存20分钟
        }
        $this->assign('random_video',$random_video);
        
        $this->display();
    }
}