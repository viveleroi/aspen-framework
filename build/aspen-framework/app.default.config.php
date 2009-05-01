<?php

/**
 * This configuration file holds application-specific configuration
 * options. These override and extend the default system configurations.
 *
 * Database and server-specific configurations should go into the config.php file.
 *
 * For more information, visit:
 * http://docs.aspen-framework.org/wiki/Aspen:Config
 */

	// application name
	$config['application_name'] = 'Aspen Framework';
	
	// the current application master GUID
	$config['application_guid'] = '8266f8e0-204d-11dd-bd0b-0800200c9a66';
	
	// application version
	$config['application_version'] = '1.1.0';
	
	// require user authentication for admin subdirectory application
	DEFINE('USER_AUTH_ADMIN', true);

?>
