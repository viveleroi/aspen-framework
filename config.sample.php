<?php

/**
 * This is a sample of what your config.php file should look
 * like. This file may be removed and is unly used for example
 * purposes.
 *
 * For more information visit:
 * http://docs.aspen-framework.org/wiki/Aspen:Installation
 * or
 * http://docs.aspen-framework.org/wiki/Aspen:Config
 */

/**
 * Determine which server we're currently running on
 */
switch($_SERVER['SERVER_NAME']){
	case 'yourwebsite.com':
	case 'www.yourwebsite.com':
		define('APP_SERVER', 'production');
		$config['db_username'] = 'root';
		$config['db_password'] = '';
		$config['db_database'] = 'aspen';
		$config['db_hostname'] = 'localhost';
		break;
	case 'dev.yourwebsite.com':
		define('APP_SERVER', 'staging');
		$config['db_username'] = 'root';
		$config['db_password'] = '';
		$config['db_database'] = 'aspen_dev';
		$config['db_hostname'] = 'localhost';
		break;
	default:
		define('APP_SERVER', 'development');
		$config['db_username'] = 'root';
		$config['db_password'] = '';
		$config['db_database'] = 'aspen';
		$config['db_hostname'] = 'localhost';
		$config['load_add_core_class'][] = array('classname' => 'Debug');
		break;
}
?>