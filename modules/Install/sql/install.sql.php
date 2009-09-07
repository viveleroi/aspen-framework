<?php

/**
 * This file holds queries to run against the
 * database for automating application
 * installation.
 */

$sql[] = "

CREATE TABLE IF NOT EXISTS `authentication` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `username` text NOT NULL,
  `nice_name` varchar(155) NOT NULL default '',
  `password` text NOT NULL,
  `latest_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `allow_login` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

";


$sql[] = "

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned default NULL,
  `config_key` varchar(155) NOT NULL default '',
  `default_value` text NOT NULL,
  `current_value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

";
	
	
$sql[] = "

CREATE TABLE IF NOT EXISTS `error_log` (
  `id` int(11) NOT NULL auto_increment,
  `application` text NOT NULL,
  `version` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `visitor_ip` text NOT NULL,
  `referer_url` text NOT NULL,
  `request_uri` text NOT NULL,
  `user_agent` text NOT NULL,
  `error_type` text NOT NULL,
  `error_file` text NOT NULL,
  `error_line` text NOT NULL,
  `error_message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

";
		

$sql[] = "

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(155) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

";

		
$sql[] = "

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guid` varchar(50) NOT NULL default '',
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

";

		
$sql[] = "

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` INT(11) unsigned NULL DEFAULT NULL,
  `group_id` int(11) unsigned NULL DEFAULT NULL,
  `interface` varchar(155) NOT NULL,
  `module` varchar(155) NOT NULL default '',
  `method` varchar(155) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

";


$sql[] = "

CREATE TABLE IF NOT EXISTS `user_group_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

";


$sql[] = "

ALTER TABLE `config`
  ADD CONSTRAINT `config_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `kiwi_trunk`.`authentication` (`id`) ON DELETE CASCADE;

";


$sql[] = "

ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `kiwi_trunk`.`groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `kiwi_trunk`.`authentication` (`id`) ON DELETE CASCADE;

";


$sql[] = "

ALTER TABLE `user_group_link`
  ADD CONSTRAINT `user_group_link_ibfk_4` FOREIGN KEY (`group_id`) REFERENCES `kiwi_trunk`.`groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_group_link_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `kiwi_trunk`.`authentication` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_group_link_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `kiwi_trunk`.`authentication` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_group_link_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `kiwi_trunk`.`authentication` (`id`) ON DELETE CASCADE;

";


$sql[] = "

INSERT INTO `config` (`id`, `user_id`, `config_key`, `default_value`, `current_value`) VALUES
(1, NULL, 'app.database.version', '', '');

";


$sql[] = "

INSERT INTO `modules` (`id`, `guid`, `sort_order`) VALUES
(1, '652d519c-b7f3-11dc-8314-0800200c9a66', 0),
(2, 'f801e330-c7ba-11dc-95ff-0800200c9a66', 0),
(3, 'eee1d8c0-d50a-11dc-95ff-0800200c9a66', 0),
(4, '007b300a-fe0c-4f7b-b36f-ef458c32753a', 0),
(5, '547c1418-8483-6c05-fe86-6517cc590c83', 0);

";


$sql[] = "

INSERT INTO `permissions` (`id`, `user_id`, `group_id`, `interface`, `module`, `method`) VALUES
(1, NULL, NULL, '', '*', '*'),
(2, NULL, NULL, '*', 'Users', 'login'),
(3, NULL, NULL, '*', 'Users', 'authenticate'),
(4, NULL, NULL, '*', 'Users', 'logout'),
(5, NULL, NULL, '*', 'Users', 'forgot'),
(6, NULL, NULL, 'Admin', 'Install', '*'),
(7, NULL, 1, '*', '*', '*'),
(8, NULL, 2, '*', 'Index', '*'),
(9, NULL, 2, '*', 'Settings', '*'),
(10, NULL, 2, '*', 'Users', 'my_account');

";


$sql[] = "

INSERT INTO `user_group_link` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1);

";


$sql[] = "

INSERT INTO `groups` (`id`, `name`) VALUES
(1, 'Administrator'),
(2, 'User');

";

?>