<?php

/**
 * This file holds queries to run against the
 * database for automating application
 * installation.
 */

$sql[] = "

CREATE TABLE IF NOT EXISTS `authentication` (
  `id` int(11) NOT NULL auto_increment,
  `username` text NOT NULL,
  `nice_name` varchar(155) NOT NULL default '',
  `password` text NOT NULL,
  `latest_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `allow_login` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";


$sql[] = "

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `config_key` varchar(155) NOT NULL default '',
  `default_value` text NOT NULL,
  `current_value` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";
		

$sql[] = "

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(155) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";

		
$sql[] = "

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guid` varchar(50) NOT NULL default '',
  `is_base_module` tinyint(1) NOT NULL,
  `autoload_with` varchar(155) NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

";

		
$sql[] = "

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  `interface` varchar(155) NOT NULL,
  `module` varchar(155) NOT NULL default '',
  `method` varchar(155) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";
	
$sql[] = "

CREATE TABLE IF NOT EXISTS `preferences_sorts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `location` varchar(155) NOT NULL default '',
  `sort_by` varchar(155) NOT NULL default '',
  `direction` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";
	
$sql[] = "

CREATE TABLE IF NOT EXISTS `upgrade_history` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `current_build` varchar(155) NOT NULL default '',
  `upgrade_completed` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";
	
$sql[] = "

CREATE TABLE IF NOT EXISTS `user_group_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";


$sql[] = "

INSERT INTO `modules` (`id`, `guid`, `is_base_module`, `autoload_with`, `sort_order`) VALUES
(1, '652d519c-b7f3-11dc-8314-0800200c9a66', 1, '', 0),
(2, 'f801e330-c7ba-11dc-95ff-0800200c9a66', 1, '', 0),
(3, 'eee1d8c0-d50a-11dc-95ff-0800200c9a66', 1, '', 0),
(4, '007b300a-fe0c-4f7b-b36f-ef458c32753a', 1, '', 0),
(5, '547c1418-8483-6c05-fe86-6517cc590c83', 1, '', 0);

";

	
$sql[] = "

INSERT INTO `permissions` (`id`, `user_id`, `group_id`, `interface`, `module`, `method`) VALUES
(1, 0, 1, '*', '*', '*');
	
";

$sql[] = "

INSERT INTO `permissions` (`id`, `user_id`, `group_id`, `interface`, `module`, `method`) VALUES
(2, 0, 2, '*', 'Index', '*'),
(3, 0, 2, '*', 'Settings', '*'),
(4, 0, 2, '*', 'Users', 'my_account');
	
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