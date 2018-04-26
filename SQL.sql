# Host: 127.0.0.1  (Version 5.6.35-log)
# Date: 2018-04-25 17:02:28
# Generator: MySQL-Front 6.0  (Build 2.20)


#
# Structure for table "demo_menu"
#
DROP TABLE IF EXISTS `demo_menu`;
CREATE TABLE `demo_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父栏目id，无为0',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '名称/标题',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '图标',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '规则，如：amin/index，或者admin/index/index',
  `sort` int(4) unsigned NOT NULL DEFAULT '1' COMMENT '排序，数值越大，排序靠前',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态,0-隐藏，1-显示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='菜单表';
#
# Data for table "demo_menu"
#
INSERT INTO `demo_menu` VALUES (1,0,'总控制台','fa-tachometer','admin/index/index',10,1),(2,0,'系统管理','fa-cog','admin/system',4,1),(3,2,'管理员列表','icon-double-angle-right','admin/system/admin',4,1),(4,2,'菜单列表','icon-double-angle-right','admin/system/menu',1,1),(5,2,'用户组列表','icon-double-angle-right','admin/system/group',3,1),(6,2,'权限列表','icon-double-angle-right','admin/system/rules',2,1);


#
# Structure for table "demo_auth_rule"
#
DROP TABLE IF EXISTS `demo_auth_rule`;
CREATE TABLE `demo_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '父id',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一标识，例：模块名/控制器名/方法名  或  自定义规则',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则描述，规则中文名称',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5 and {score}<100 表示用户的分数在5-100之间时这条规则才会通过。（默认为1）',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态，1-正常。0-禁用',
  `condition` char(100) NOT NULL DEFAULT '' COMMENT '规则表达式，为空标识存在就验证，不为空表示按照条件验证。当type为1时，condition字段里面的内容将会用作正则表达式的规则来配合认证规则来认证用户',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='权限验证规则表';
#
# Data for table "demo_auth_rule"
#
INSERT INTO `demo_auth_rule` VALUES (1,0,'访问控制台','admin/index/index',1,1,'');


#
# Structure for table "emmet_auth_group_access"
#
DROP TABLE IF EXISTS `emmet_auth_group_access`;
CREATE TABLE `emmet_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组明细表';


#
# Structure for table "emmet_auth_group"
#
DROP TABLE IF EXISTS `emmet_auth_group`;
CREATE TABLE `emmet_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(200) DEFAULT NULL COMMENT '描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态，1-正常，0-禁用',
  `rules` text NOT NULL COMMENT '用户组拥有的规则id，多个规则用“,”隔开',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='权限控制-用户组表';


#
# Structure for table "demo_admin"
#
DROP TABLE IF EXISTS `demo_admin`;
CREATE TABLE `demo_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '管理员名称',
  `password` varchar(50) NOT NULL DEFAULT '' COMMENT '管理员密码',
  `last_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上次登录时间',
  `last_ip` varchar(20) DEFAULT NULL COMMENT '上次登录ip',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员表';
#
# Data for table "demo_admin"
#
INSERT INTO `demo_admin` VALUES (1,'system','e10adc3949ba59abbe56e057f20f883e',1524645815,'127.0.0.1',1);
