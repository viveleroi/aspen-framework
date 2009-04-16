<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Define information required in bootstrap, application
 */
// define our application paths
define('SYSTEM_PATH', dirname(__FILE__));
define('APPLICATION_PATH', str_replace(DIRECTORY_SEPARATOR . "system", '', SYSTEM_PATH));
define('MODULES_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules');
define('PLUGINS_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'plugins');

// here we'll quicly check for absolute minimal php5 support
if(version_compare(phpversion(), "5.1.0", 'ge')){
	
	// Include the bootstrap file
	require(SYSTEM_PATH.DIRECTORY_SEPARATOR.'bootstrap.php');
	
	/**
	 * @abstract Verifies and loads a module, and returns it as an object.
	 * @param text $module Name of a module to load
	 * @return mixed
	 */
	function load_module($module = false){
	
		define('LOADING_SECTION', '');
		define('INTERFACE_PATH', APPLICATION_PATH);
		define('INCLUDE_ONLY', true);
		
		if($module){
			
			$module = ucwords(strtolower($module));
			
			$loc = MODULES_PATH.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.$module.".php";
			if(!defined('INCLUDE_ONLY_PATH')){
				define('INCLUDE_ONLY_PATH', $loc);
			}
			
			if(file_exists(INCLUDE_ONLY_PATH)){
	
				if(require(INCLUDE_ONLY_PATH)){
					// load our config files
					$config = Bootstrap::loadAllConfigs();
	
					$module_object = new $module($config);
					return $module_object;
					
				}
			} else {
				
				trigger_error("Module ". $module ." could not be found.", E_USER_ERROR);
				
			}
		}
		
		return false;
		
	}
	
	
	/**
	 * @abstract Returns the pure bootstrap application
	 * @return mixed
	 */
	function load_framework($interface = false){
	
		define('LOADING_SECTION', ucwords($interface));
		define('INTERFACE_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . strtolower(LOADING_SECTION));

		$config = Bootstrap::loadAllConfigs();
		return new Bootstrap($config);
	}
	
} else {

	trigger_error('This application requires PHP 5.1+. Please upgrade your version of PHP.', E_USER_ERROR);
	
}
?>