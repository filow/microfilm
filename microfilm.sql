-- MySQL dump 10.13  Distrib 5.5.37, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: microfilm
-- ------------------------------------------------------
-- Server version	5.5.37-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mf_admin`
--

DROP TABLE IF EXISTS `mf_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `uid` varchar(150) NOT NULL COMMENT '管理员登录账号',
  `password` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT '管理员密码hash',
  `realname` varchar(30) NOT NULL COMMENT '管理员姓名',
  `is_forbidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否被禁用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `is_forbidden` (`is_forbidden`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_admin`
--

LOCK TABLES `mf_admin` WRITE;
/*!40000 ALTER TABLE `mf_admin` DISABLE KEYS */;
INSERT INTO `mf_admin` VALUES (1,'sman','5457f4314ab1f2593b83db82d894c68e','开发阶段超级管理员',0);
/*!40000 ALTER TABLE `mf_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_attach`
--

DROP TABLE IF EXISTS `mf_attach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_attach` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '附件ID',
  `filename` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '附件名称',
  `access_key` char(32) NOT NULL COMMENT '批次KEY',
  `time` int(11) NOT NULL COMMENT '上传时间',
  `file_location` varchar(255) DEFAULT NULL COMMENT '文件位置',
  `is_image` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为图片',
  `user_id` int(11) NOT NULL COMMENT '所属用户的ID(仅适用于头像等缩略图)',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='附件';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_attach`
--

LOCK TABLES `mf_attach` WRITE;
/*!40000 ALTER TABLE `mf_attach` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_attach` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_config`
--

DROP TABLE IF EXISTS `mf_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `var` varchar(50) NOT NULL COMMENT '变量名',
  `value` varchar(150) CHARACTER SET utf8 NOT NULL COMMENT '变量值',
  `comment` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '注释',
  `range` tinyint(2) NOT NULL DEFAULT '0' COMMENT '使用范围（0-全局，1-Index,2-Manage）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `var` (`var`),
  KEY `range` (`range`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 COMMENT='系统设置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_config`
--

LOCK TABLES `mf_config` WRITE;
/*!40000 ALTER TABLE `mf_config` DISABLE KEYS */;
INSERT INTO `mf_config` VALUES (1,'UPLOAD_DATE_FROM','1388505660','上传时间起始点',0),(2,'UPLOAD_DATE_TO','1398787440','上传时间终止点',0),(5,'VOTE_DATE_FROM','1386663198','投票起始日期',0),(6,'VOTE_DATE_TO','1398189840','投票截至日期',0),(7,'VOTE_NUMBER','5','每个ip能投票的次数总和',0),(8,'VOTE_PROFESSOR_DATE_FROM','1386663421','专家评审时间起始',0),(9,'VOTE_PROFESSOR_DATE_TO','1398600240','专家评审时间终止',0),(10,'LOGIN_FAIL_TIME_VERIFY','2','允许的最大登录失败次数（达到后会出现验证码）',2),(11,'LOGIN_FAIL_TIME_WAIT','5','允许的最大登录失败次数（达到后会有一段时间不允许尝试）',2),(12,'LOGIN_FAIL_TIMEOUT','180','达到限制次数后暂停登录尝试的等待时间（秒）',2),(13,'LOGIN_FAIL_TIME_SPAN','60','判定连续登录的时间间隔（秒）',2),(14,'AJAX_VERIFY','0','是否对AJAX方法启用验证',2),(15,'VISIT_COUNT','50','网站访问总量',0),(16,'BAIDU_AK','gqv4hLcw48y5zr8cMg33Vh9C','百度播放器APPKEY',0),(17,'BAIDU_SK','8z5NRKkz11O6cSAx','百度播放器SCREATKEY',0);
/*!40000 ALTER TABLE `mf_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_exp_video`
--

DROP TABLE IF EXISTS `mf_exp_video`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_exp_video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) CHARACTER SET utf8 NOT NULL COMMENT '示例视频名称',
  `location` varchar(255) NOT NULL COMMENT '示例视频的地址',
  `thumb` varchar(255) DEFAULT NULL COMMENT '缩略图',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='示例视频';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_exp_video`
--

LOCK TABLES `mf_exp_video` WRITE;
/*!40000 ALTER TABLE `mf_exp_video` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_exp_video` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_intro`
--

DROP TABLE IF EXISTS `mf_intro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_intro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL COMMENT '显示名称',
  `passage_id` int(11) NOT NULL COMMENT '文章id',
  `anchor` varchar(20) NOT NULL COMMENT '锚点',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='首页大赛介绍项目表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_intro`
--

LOCK TABLES `mf_intro` WRITE;
/*!40000 ALTER TABLE `mf_intro` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_intro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_judge`
--

DROP TABLE IF EXISTS `mf_judge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_judge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opus_id` int(11) NOT NULL COMMENT '作品id',
  `judger_id` int(11) NOT NULL COMMENT '评委id，与admin表关联',
  `rank` int(11) NOT NULL COMMENT '评分',
  `comment` text COMMENT '评语',
  PRIMARY KEY (`id`),
  KEY `judger_id` (`judger_id`),
  KEY `opus_id` (`opus_id`),
  CONSTRAINT `mf_judge_ibfk_1` FOREIGN KEY (`opus_id`) REFERENCES `mf_opus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mf_judge_ibfk_2` FOREIGN KEY (`judger_id`) REFERENCES `mf_admin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评委评分表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_judge`
--

LOCK TABLES `mf_judge` WRITE;
/*!40000 ALTER TABLE `mf_judge` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_judge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_msg`
--

DROP TABLE IF EXISTS `mf_msg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '消息id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `title` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '消息标题',
  `content` text CHARACTER SET utf8 NOT NULL COMMENT '消息内容',
  `has_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读',
  `time` int(11) NOT NULL COMMENT '发送时间',
  `from` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '发送人',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`has_read`),
  CONSTRAINT `mf_msg_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `mf_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户消息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_msg`
--

LOCK TABLES `mf_msg` WRITE;
/*!40000 ALTER TABLE `mf_msg` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_msg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_notification`
--

DROP TABLE IF EXISTS `mf_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统消息id',
  `title` varchar(255) NOT NULL COMMENT '系统消息标题',
  `content` text NOT NULL COMMENT '内容',
  `content_notag` text COMMENT '没有标签的内容',
  `valid_from` int(11) DEFAULT NULL COMMENT '生效起始时间',
  `date` int(11) NOT NULL COMMENT '发布时间',
  `force_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '强制置顶',
  `force_hide` tinyint(1) NOT NULL DEFAULT '0' COMMENT '使其隐藏',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`),
  KEY `valid_from` (`valid_from`),
  KEY `force_top` (`force_top`,`force_hide`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统消息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_notification`
--

LOCK TABLES `mf_notification` WRITE;
/*!40000 ALTER TABLE `mf_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_notification_attach`
--

DROP TABLE IF EXISTS `mf_notification_attach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_notification_attach` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '系统通知附件ID',
  `filename` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '文件名',
  `file_location` varchar(255) NOT NULL COMMENT '文件路径',
  `admin_id` int(11) NOT NULL COMMENT '管理员ID',
  `is_image` tinyint(1) NOT NULL COMMENT '是否图像',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='通知附件表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_notification_attach`
--

LOCK TABLES `mf_notification_attach` WRITE;
/*!40000 ALTER TABLE `mf_notification_attach` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_notification_attach` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_opus`
--

DROP TABLE IF EXISTS `mf_opus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_opus` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '作品id',
  `user_id` int(11) NOT NULL COMMENT '作品所属用户id',
  `opus_name` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '作品名称',
  `content` text CHARACTER SET utf8 COMMENT '对作品的介绍',
  `belong` int(11) NOT NULL DEFAULT '0' COMMENT '课程所属专业ID',
  `youkuid` varchar(32) DEFAULT NULL COMMENT '作品所在的优酷ID',
  `add_time` int(11) NOT NULL COMMENT '上传时间',
  `vote_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分数',
  `comment_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论总数',
  `view_count` int(11) NOT NULL DEFAULT '0' COMMENT '浏览数',
  `popularity` mediumint(9) NOT NULL DEFAULT '0' COMMENT '受欢迎度(由程序自己计算)',
  `overview` varchar(128) CHARACTER SET utf8 DEFAULT NULL COMMENT '内容概览',
  `thumb` int(11) DEFAULT NULL COMMENT '作品主缩略图附属附件ID',
  `thumb_mini` int(11) DEFAULT NULL COMMENT '小缩略图附属附件ID',
  `force_top` tinyint(4) NOT NULL DEFAULT '1' COMMENT '提前权重（1-9）',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-正常，1-草稿，2-被禁用',
  `init` tinyint(1) NOT NULL DEFAULT '1' COMMENT '初始化标签（防止反复刷新创建草稿）',
  `last_refesh` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次刷新作品信息的时间',
  PRIMARY KEY (`id`),
  KEY `userid` (`user_id`,`add_time`,`vote_count`,`comment_count`,`force_top`),
  KEY `view_count` (`view_count`),
  KEY `belong` (`belong`),
  KEY `last_refesh` (`last_refesh`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户作品表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_opus`
--

LOCK TABLES `mf_opus` WRITE;
/*!40000 ALTER TABLE `mf_opus` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_opus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_opus_attach`
--

DROP TABLE IF EXISTS `mf_opus_attach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_opus_attach` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '作品附件ID',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID(非必须)',
  `opus_id` int(11) NOT NULL COMMENT '作品ID',
  `attach_id` int(11) NOT NULL COMMENT '附件ID',
  `type` enum('T','D','V') NOT NULL COMMENT '附件类型（T-缩略图，V-视频，D-文档）',
  PRIMARY KEY (`id`),
  KEY `attach_id` (`attach_id`),
  KEY `opus_id` (`opus_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mf_opus_attach_ibfk_3` FOREIGN KEY (`attach_id`) REFERENCES `mf_attach` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='作品附件表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_opus_attach`
--

LOCK TABLES `mf_opus_attach` WRITE;
/*!40000 ALTER TABLE `mf_opus_attach` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_opus_attach` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_opus_author`
--

DROP TABLE IF EXISTS `mf_opus_author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_opus_author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opus_id` int(11) NOT NULL COMMENT '作品ID',
  `author` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '作者姓名',
  `sex` tinyint(1) DEFAULT NULL COMMENT '0-男性，1-女性',
  `idcard` varchar(18) DEFAULT NULL COMMENT '身份证号码',
  `belong` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '所属部门',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号码',
  `email` varchar(80) CHARACTER SET utf8 DEFAULT NULL COMMENT '电子邮箱',
  PRIMARY KEY (`id`),
  KEY `opus_id` (`opus_id`),
  CONSTRAINT `mf_opus_author_ibfk_1` FOREIGN KEY (`opus_id`) REFERENCES `mf_opus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='作品作者表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_opus_author`
--

LOCK TABLES `mf_opus_author` WRITE;
/*!40000 ALTER TABLE `mf_opus_author` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_opus_author` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_opus_comment`
--

DROP TABLE IF EXISTS `mf_opus_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_opus_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `opus_id` int(11) NOT NULL COMMENT '所属作品ID',
  `user_id` int(11) NOT NULL COMMENT '评论人ID',
  `message` text CHARACTER SET utf8 NOT NULL COMMENT '评论内容',
  `date` int(11) NOT NULL COMMENT '评论时间',
  `force_hide` tinyint(1) NOT NULL DEFAULT '0' COMMENT '强制隐藏',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `opus_id` (`opus_id`),
  KEY `force_hide` (`force_hide`),
  CONSTRAINT `mf_opus_comment_ibfk_1` FOREIGN KEY (`opus_id`) REFERENCES `mf_opus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mf_opus_comment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `mf_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='作品评论表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_opus_comment`
--

LOCK TABLES `mf_opus_comment` WRITE;
/*!40000 ALTER TABLE `mf_opus_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_opus_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_opus_view`
--

DROP TABLE IF EXISTS `mf_opus_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_opus_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opus_id` int(11) NOT NULL COMMENT '作品ID',
  `ip` varchar(30) NOT NULL COMMENT '用户IP',
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `opus_id` (`opus_id`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='用户查看作品表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_opus_view`
--

LOCK TABLES `mf_opus_view` WRITE;
/*!40000 ALTER TABLE `mf_opus_view` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_opus_view` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_opus_vote`
--

DROP TABLE IF EXISTS `mf_opus_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_opus_vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评分ID',
  `opus_id` int(11) NOT NULL COMMENT '作品ID',
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `user_ip` varchar(30) NOT NULL COMMENT '投票人IP',
  `date` int(11) NOT NULL COMMENT '投票时间',
  PRIMARY KEY (`id`),
  KEY `opus_id` (`opus_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mf_opus_vote_ibfk_1` FOREIGN KEY (`opus_id`) REFERENCES `mf_opus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='投票表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_opus_vote`
--

LOCK TABLES `mf_opus_vote` WRITE;
/*!40000 ALTER TABLE `mf_opus_vote` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_opus_vote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_rbac_access`
--

DROP TABLE IF EXISTS `mf_rbac_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_rbac_access` (
  `role_id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`),
  CONSTRAINT `mf_rbac_access_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `mf_rbac_role` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mf_rbac_access_ibfk_2` FOREIGN KEY (`node_id`) REFERENCES `mf_rbac_node` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_rbac_access`
--

LOCK TABLES `mf_rbac_access` WRITE;
/*!40000 ALTER TABLE `mf_rbac_access` DISABLE KEYS */;
INSERT INTO `mf_rbac_access` VALUES (2,4),(2,5),(2,35),(2,36),(2,37),(2,38),(1,4),(1,5),(1,1),(1,2),(1,3),(1,9),(1,6),(1,7),(1,8),(1,18),(1,19),(1,20),(1,10),(1,23),(1,11),(1,26),(1,25),(1,33),(1,35),(1,36),(1,39),(1,12),(1,28),(1,34),(1,37),(1,38),(1,14),(1,24),(1,16),(1,17),(1,21);
/*!40000 ALTER TABLE `mf_rbac_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_rbac_node`
--

DROP TABLE IF EXISTS `mf_rbac_node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_rbac_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `title` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `sort` smallint(6) unsigned DEFAULT NULL,
  `pid` int(11) NOT NULL DEFAULT '0',
  `is_nav` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为菜单项',
  `icon` varchar(20) DEFAULT NULL COMMENT '功能对应的图标（对应UI库的icon）',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `name` (`name`),
  KEY `is_nav` (`is_nav`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_rbac_node`
--

LOCK TABLES `mf_rbac_node` WRITE;
/*!40000 ALTER TABLE `mf_rbac_node` DISABLE KEYS */;
INSERT INTO `mf_rbac_node` VALUES (1,'Permission','后台管理员设置',NULL,20,0,1,'lock'),(2,'Index','权限系统信息',NULL,10,1,1,'info'),(3,'modify_user','修改用户信息权限',NULL,20,1,0,NULL),(4,'Index','首 页',NULL,10,0,1,'home'),(5,'Index','系统基本信息',NULL,10,4,1,'info-circle'),(6,'view_role','查看职位信息',NULL,30,1,0,NULL),(7,'add_admin','增加管理员',NULL,40,1,0,NULL),(8,'delete_admin','删除管理员',NULL,50,1,0,NULL),(9,'Node_list','系统权限列表',NULL,21,1,1,'list'),(10,'User','用户管理',NULL,30,0,1,'user'),(11,'Passage','通知公告',NULL,40,0,1,'envelope'),(12,'Opus','作品管理',NULL,50,0,1,'film'),(14,'Cmt','评论管理',NULL,70,0,1,'comment-o'),(16,'Config','系统设置',NULL,100,0,1,'cog'),(17,'Index','系统设置信息',NULL,10,16,1,NULL),(18,'add_role','增加职位',NULL,80,1,0,NULL),(19,'edit_role','修改职位信息',NULL,90,1,0,NULL),(20,'delete_role','删除职位',NULL,100,1,0,NULL),(21,'edit_config','编辑设置项',NULL,20,16,0,NULL),(22,'show_config','显示所有设置项',NULL,40,16,1,NULL),(23,'Index','用户信息列表',NULL,10,10,1,'list'),(24,'Index','评论列表',NULL,10,14,1,NULL),(25,'newPassage','编写新文章',NULL,10,11,1,'mail-forward'),(26,'passageList','文章列表',NULL,9,11,1,NULL),(28,'Index','作品列表',NULL,10,12,1,NULL),(33,'sendmsg','发送站内消息',NULL,20,11,0,''),(34,'votemanage','投票管理',NULL,30,12,1,'heart'),(35,'Judge','评委管理',NULL,45,0,1,'star'),(36,'OpusList','作品列表',NULL,10,35,1,'th-list'),(37,'account','账户管理',NULL,60,0,1,'user'),(38,'Index','账户信息修改',NULL,10,37,1,'edit'),(39,'VIEW_JUDGE','查看所有作品评分',NULL,20,35,0,NULL);
/*!40000 ALTER TABLE `mf_rbac_node` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_rbac_role`
--

DROP TABLE IF EXISTS `mf_rbac_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_rbac_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启用（1-启用 0-关闭）',
  `remark` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_rbac_role`
--

LOCK TABLES `mf_rbac_role` WRITE;
/*!40000 ALTER TABLE `mf_rbac_role` DISABLE KEYS */;
INSERT INTO `mf_rbac_role` VALUES (1,1,'系统管理员'),(2,1,'评委');
/*!40000 ALTER TABLE `mf_rbac_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_rbac_to_role`
--

DROP TABLE IF EXISTS `mf_rbac_to_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_rbac_to_role` (
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mf_rbac_to_role_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `mf_rbac_role` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mf_rbac_to_role_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `mf_admin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_rbac_to_role`
--

LOCK TABLES `mf_rbac_to_role` WRITE;
/*!40000 ALTER TABLE `mf_rbac_to_role` DISABLE KEYS */;
INSERT INTO `mf_rbac_to_role` VALUES (1,1);
/*!40000 ALTER TABLE `mf_rbac_to_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_session`
--

DROP TABLE IF EXISTS `mf_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_session` (
  `session_id` varchar(255) NOT NULL,
  `session_expire` int(11) NOT NULL,
  `session_data` blob,
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_session`
--

LOCK TABLES `mf_session` WRITE;
/*!40000 ALTER TABLE `mf_session` DISABLE KEYS */;
INSERT INTO `mf_session` VALUES ('dlbt9pi5mjdvulorrqr29s5vj7',1399367124,'');
/*!40000 ALTER TABLE `mf_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mf_user`
--

DROP TABLE IF EXISTS `mf_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mf_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(30) CHARACTER SET utf8 NOT NULL COMMENT '用户英文id',
  `nickname` varchar(20) CHARACTER SET utf8 NOT NULL COMMENT '用户姓名',
  `password` char(32) NOT NULL COMMENT '密码hash值',
  `intro` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '用户简介',
  `sex` tinyint(1) DEFAULT '0' COMMENT '性别(0-男，1-女)',
  `idcard` char(18) NOT NULL COMMENT '身份证号码',
  `belong_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-其他用户，1-本校学生',
  `department` varchar(30) CHARACTER SET utf8 DEFAULT NULL COMMENT '所属部门',
  `phone` bigint(20) NOT NULL COMMENT '手机号码',
  `email` varchar(150) NOT NULL COMMENT '电子邮箱',
  `is_first_login` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否首次登录',
  `forbidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁止登录',
  `opus_count` smallint(6) NOT NULL DEFAULT '0' COMMENT '上传作品数',
  `msg_unread` mediumint(9) NOT NULL DEFAULT '0' COMMENT '未读消息数',
  `avatar` int(11) NOT NULL COMMENT '头像对应的附件表ID',
  `avatar_large` int(11) NOT NULL COMMENT '大头像所属的附件ID',
  `popularity` int(11) NOT NULL DEFAULT '0' COMMENT '选手热度',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`uid`),
  KEY `forbidden` (`forbidden`),
  KEY `popularity` (`popularity`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mf_user`
--

LOCK TABLES `mf_user` WRITE;
/*!40000 ALTER TABLE `mf_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `mf_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-05-06 21:55:56
