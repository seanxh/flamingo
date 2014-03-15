/*
Navicat MySQL Data Transfer

Source Server         : localhost1212
Source Server Version : 50508
Source Host           : localhost:3306
Source Database       : monitor

Target Server Type    : MYSQL
Target Server Version : 50508
File Encoding         : 65001

Date: 2014-03-15 22:03:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `alert_deploy`
-- ----------------------------
DROP TABLE IF EXISTS `alert_deploy`;
CREATE TABLE `alert_deploy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of alert_deploy
-- ----------------------------
INSERT INTO `alert_deploy` VALUES ('1', '测试');

-- ----------------------------
-- Table structure for `alert_receiver`
-- ----------------------------
DROP TABLE IF EXISTS `alert_receiver`;
CREATE TABLE `alert_receiver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_deploy_id` int(11) NOT NULL,
  `receiver` varchar(30) NOT NULL,
  `rule` varchar(50) NOT NULL,
  `type` char(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of alert_receiver
-- ----------------------------
INSERT INTO `alert_receiver` VALUES ('1', '1', 'xuhao05', '', 'msg');
INSERT INTO `alert_receiver` VALUES ('2', '1', 'xuhao05', '', 'mail');

-- ----------------------------
-- Table structure for `api_status`
-- ----------------------------
DROP TABLE IF EXISTS `api_status`;
CREATE TABLE `api_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interface` varchar(200) NOT NULL,
  `http_code` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of api_status
-- ----------------------------

-- ----------------------------
-- Table structure for `database_config`
-- ----------------------------
DROP TABLE IF EXISTS `database_config`;
CREATE TABLE `database_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` char(15) NOT NULL,
  `dbname` varchar(30) NOT NULL,
  `host` varchar(150) NOT NULL,
  `port` int(11) NOT NULL,
  `user` varchar(30) NOT NULL,
  `passwd` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of database_config
-- ----------------------------
INSERT INTO `database_config` VALUES ('1', 'mysql', 'monitor', '127.0.0.1', '3306', 'root', 'root');

-- ----------------------------
-- Table structure for `log_config`
-- ----------------------------
DROP TABLE IF EXISTS `log_config`;
CREATE TABLE `log_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_name` varchar(200) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `database_id` int(11) NOT NULL,
  `time_column` varchar(50) NOT NULL,
  `log_cycle` int(11) NOT NULL,
  `log_type` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of log_config
-- ----------------------------
INSERT INTO `log_config` VALUES ('1', 'mc', 'mc_status', '1', 'add_time', '60', '0');
INSERT INTO `log_config` VALUES ('2', 'api', 'api_status', '1', 'ctime', '60', '0');
INSERT INTO `log_config` VALUES ('3', 'queue', 'queue', '1', 'ctime', '0', '1');

-- ----------------------------
-- Table structure for `mc_status`
-- ----------------------------
DROP TABLE IF EXISTS `mc_status`;
CREATE TABLE `mc_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(70) NOT NULL,
  `port` int(11) NOT NULL,
  `connections` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mc_status
-- ----------------------------
INSERT INTO `mc_status` VALUES ('1', '127.0.0.1', '8080', '1000', '2014-03-09 23:19:00');
INSERT INTO `mc_status` VALUES ('2', '127.0.0.1', '8090', '2000', '2014-03-09 23:19:10');
INSERT INTO `mc_status` VALUES ('3', '127.0.0.1', '8080', '100', '2014-03-09 23:18:00');
INSERT INTO `mc_status` VALUES ('4', '127.0.0.1', '8090', '200', '2014-03-09 23:18:00');

-- ----------------------------
-- Table structure for `monitor_condition`
-- ----------------------------
DROP TABLE IF EXISTS `monitor_condition`;
CREATE TABLE `monitor_condition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) NOT NULL,
  `logic_operator` char(10) NOT NULL,
  `comparison_operator` char(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of monitor_condition
-- ----------------------------
INSERT INTO `monitor_condition` VALUES ('1', '1', 'and', '>=');
INSERT INTO `monitor_condition` VALUES ('2', '1', 'and', 'in');
INSERT INTO `monitor_condition` VALUES ('3', '3', 'and', '>');

-- ----------------------------
-- Table structure for `monitor_operation_expression`
-- ----------------------------
DROP TABLE IF EXISTS `monitor_operation_expression`;
CREATE TABLE `monitor_operation_expression` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `condition_id` int(11) NOT NULL,
  `left_or_right` char(6) NOT NULL,
  `expression` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of monitor_operation_expression
-- ----------------------------
INSERT INTO `monitor_operation_expression` VALUES ('1', '1', 'left', 'prev(connections,array(ip,port),1)');
INSERT INTO `monitor_operation_expression` VALUES ('2', '1', 'right', '50');
INSERT INTO `monitor_operation_expression` VALUES ('3', '2', 'left', '$ip');
INSERT INTO `monitor_operation_expression` VALUES ('4', '2', 'right', '{127.0.0.1,192.168.0.1}');
INSERT INTO `monitor_operation_expression` VALUES ('5', '3', 'left', '$size');
INSERT INTO `monitor_operation_expression` VALUES ('6', '3', 'right', '10');

-- ----------------------------
-- Table structure for `monitor_rule`
-- ----------------------------
DROP TABLE IF EXISTS `monitor_rule`;
CREATE TABLE `monitor_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL,
  `monitor_name` varchar(100) NOT NULL,
  `filter_fields` varchar(500) NOT NULL,
  `filter_conditions` varchar(500) NOT NULL,
  `is_alert_everytime` tinyint(4) NOT NULL,
  `alert_in_cycles` int(11) NOT NULL,
  `alert_when_gt_times` int(11) NOT NULL,
  `alert_title` varchar(2000) NOT NULL,
  `alert_head` varchar(2000) NOT NULL,
  `alert_content` varchar(2000) NOT NULL,
  `alert_receiver` varchar(500) NOT NULL,
  `alert_deploy_id` int(11) NOT NULL,
  `wait_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of monitor_rule
-- ----------------------------
INSERT INTO `monitor_rule` VALUES ('1', '1', 'mc连接数', '', '', '1', '0', '0', '共有[count()]台MC连接数过高 [$ip]:[$port]', '<tr><td>IP</td><td>端口</td><td>连接数</td><td>上一分钟连接数</td><td>时间</td></tr>', '<tr><td>[$ip]</td><td>[$port]</td><td>[$connections]</td><td>[prev(connections,{ip,port})]</td><td>[$add_time]</tr>', 'xuhao05', '1', '10', '1');
INSERT INTO `monitor_rule` VALUES ('2', '2', '接口监控', '', '', '0', '3', '3', '接口报警', '', '<tr><td>{$interface}</td><td>{$http_code}</td><td>{$message}</td></tr>', 'xuhao05', '0', '10', '1');
INSERT INTO `monitor_rule` VALUES ('3', '3', 'queue_monitor', '', 'ctime>=\'2014-03-09 23:00:00\' and ctime<=\'2014-03-09 23:30:00\' and status=0', '0', '0', '0', '共有[count()]个队列堆积过高', '<tr><td>queue</td><td>stack</td><td>状态</td><td>时间</td></tr>', '<tr><td>[$queue_name]</td><td>[$size]</td><td>[$status]</td><td>[$ctime]</tr>', '', '1', '0', '1');

-- ----------------------------
-- Table structure for `monitor_rule_join`
-- ----------------------------
DROP TABLE IF EXISTS `monitor_rule_join`;
CREATE TABLE `monitor_rule_join` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) NOT NULL,
  `table_name` varchar(70) NOT NULL,
  `left_condition` varchar(100) NOT NULL,
  `right_condition` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of monitor_rule_join
-- ----------------------------

-- ----------------------------
-- Table structure for `queue`
-- ----------------------------
DROP TABLE IF EXISTS `queue`;
CREATE TABLE `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `size` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `queue_name` varchar(200) NOT NULL,
  `ctime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of queue
-- ----------------------------
INSERT INTO `queue` VALUES ('1', '900', '0', 'test', '2014-03-09 23:19:00');
INSERT INTO `queue` VALUES ('2', '900', '0', 'test', '2014-03-09 23:18:00');
