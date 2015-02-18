SET NAMES utf8;
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `sock_banned_users`;
CREATE TABLE `sock_banned_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `username` varchar(256) DEFAULT NULL,
  `expiration` bigint(20) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `sock_channels`;
CREATE TABLE `sock_channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chname` varchar(256) NOT NULL,
  `pwd` varchar(512) DEFAULT NULL,
  `priv` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`chname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `sock_logs`;
CREATE TABLE `sock_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `epoch` int(11) DEFAULT NULL,
  `userid` int(11) NOT NULL,
  `username` varchar(256) NOT NULL,
  `color` varchar(24) NOT NULL,
  `channel` varchar(1024) NOT NULL,
  `chrank` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `flags` varchar(10) NOT NULL DEFAULT '10010',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `sock_online_users`;
CREATE TABLE `sock_online_users` (
  `userid` int(11) NOT NULL,
  `username` varchar(256) NOT NULL,
  `color` varchar(16) NOT NULL,
  `perms` varchar(512) NOT NULL,
  UNIQUE KEY `userid` (`userid`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;