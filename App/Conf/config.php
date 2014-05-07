<?php
return array(
	//应用分组设置
	'APP_GROUP_LIST'	=> 'Index,Manage',
	'DEFAULT_GROUP'		=> 'Index',
	'APP_GROUP_MODE'	=> 1,  //采用独立分组方案
	'APP_GROUP_PATH'	=> 'Modules',

	//数据库设置
	"DB_TYPE" 			=> "mysqli",
    "DB_HOST" 			=> "127.0.0.1",
    "DB_NAME" 			=> "microfilm",
    "DB_USER" 			=> "root",
    // "DB_PWD" 			=> "root",
    "DB_PORT"			=> "3306",
    "DB_PREFIX" 		=> "mf_",

    //用DB存储session
    'SESSION_TYPE'		=> 'Db',
    "session_options" => array('expire'=>1800),

    //应用内部设置（DB方式存储）
    //DB设置生存期
    'EXTERNAL_CONFIG_CACHE_LIFETIME'=>1200,  //20分钟
    // DEBUG模式下生存期
    'EXTERNAL_CONFIG_CACHE_LIFETIME_DEBUG'=>300,  //5分钟
    'INDEX_CACHE_LIFTTIME' => 300,//5分钟

	//模板设置
	'TMPL_PARSE_STRING'	=> array(
        '__PUBLIC__'    => __ROOT__ . '/Public',
		'__PLUGIN__'	=> __ROOT__ . '/Public/plugins',
        '__UPLOAD__'    => __ROOT__ . '/Upload',
		'__NAME__'		=> '大学生微电影大赛网站',
		'__NAME-EN__'	=> 'microfilm',
        '__DEPART__'    => '李仁海 陈洁 徐长栋小组，中国大学生计算机设计大赛'
		),    
    'URL_CASE_INSENSITIVE' =>true,
	'PASSWORD_SALT'     => '!DXSWDYDS^@2c',
	//显示页面trace信息
	'SHOW_PAGE_TRACE'	=> true,

    'TMPL_EXCEPTION_FILE' => './App/Tpl/exception.html',
    'TMPL_ACTION_ERROR' => './App/Tpl/jump.html',
    'TMPL_ACTION_SUCCESS' => './App/Tpl/jump.html',

    // 'URL_REWRITE_SWITCH' => true,
);
?>