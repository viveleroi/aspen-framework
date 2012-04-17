<?php

/**
 * This sample configuration file holds installation-specific configuration
 * options. These override and extend the default system configurations and
 * the application configurations.
 *
 * For more information, visit:
 * http://docs.aspen-framework.org/wiki/Aspen:Config
 */

/**
 * Based on the environment, the configuration system will pass the default
 * configuration object to one of the below methods. You may set/extend
 * the configuration system and return it to the application. 
 */
class UserConfig extends UserConfigurable {
	
	
	/**
	 * List all domains, IPs, etc associated with each environment. When
	 * auto-detected, the configuration system will automatically
	 * call the appropriate methods below for each env.
	 * 
	 * @var array 
	 */
	protected $production	= array('your-domain.com');
	protected $staging		= array('beta.your-domain.com');
	
	
	/**
	 * Production Server
	 * @return type 
	 */
	protected function production( $config ){
		
		$config->set('db_hostname', '127.0.0.1');
		$config->set('db_username', 'root');
		$config->set('db_password', '');
		$config->set('db_database', 'aspen-framework');
		
		return $config;
		
	}
	
	
	/**
	 * Staging Server
	 * @return type 
	 */
	protected function staging($config){
		
		$config->set('db_hostname', '127.0.0.1');
		$config->set('db_username', 'root');
		$config->set('db_password', '');
		$config->set('db_database', 'aspen-framework');
		
		return $config;
		
	}
	
	
	/**
	 * Development Server
	 * @return type 
	 */
	protected function development($config){
		
		$config->set('db_hostname', '127.0.0.1');
		$config->set('db_username', 'root');
		$config->set('db_password', '');
		$config->set('db_database', 'aspen-framework');

		return $config;
		
	}
}