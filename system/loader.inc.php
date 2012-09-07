<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Define information required in bootstrap, application
 */
// define our application paths
define('SYSTEM_PATH', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
define('APPLICATION_PATH', str_replace(DS . "system", '', SYSTEM_PATH));
define('MODULES_PATH', APPLICATION_PATH . DS . 'modules');
define('PLUGINS_PATH', APPLICATION_PATH . DS . 'plugins');

// set execution start time
define('EXECUTION_START', microtime());

// here we'll quicly check for absolute minimal php5 support
if(version_compare(phpversion(), "5.1.0", 'ge')){
	
	// Include the bootstrap file
	require(SYSTEM_PATH.DIRECTORY_SEPARATOR.'bootstrap.php');
	
	/**
	 * Verifies and loads a module, and returns it as an object.
	 * @param text $module Name of a module to load
	 * @return mixed
	 */
	function load_module($module = false){
	
		define('LOADING_SECTION', '');
		define('INCLUDE_ONLY', true);
		
		if($module){
			
			$module = ucwords(strtolower($module));
			
			$loc = MODULES_PATH.DS.$module.DS.$module.".php";
			if(!defined('INCLUDE_ONLY_PATH')){
				define('INCLUDE_ONLY_PATH', $loc);
			}
			
			if(file_exists(INCLUDE_ONLY_PATH)){
	
				if(require(INCLUDE_ONLY_PATH)){
					
					// Run the config loader - which returns complete Default -> App -> Config object.
					include(SYSTEM_PATH . DS . 'config' . DS . 'ConfigLoader.php');
					$config = ConfigLoader::load();
	
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
	 * Returns the pure bootstrap application
	 * @return mixed
	 */
	function load_framework($interface = false){
	
		define('LOADING_SECTION', ucwords($interface));

		// Run the config loader - which returns complete Default -> App -> Config object.
		include(SYSTEM_PATH . DS . 'config' . DS . 'ConfigLoader.php');
		$config = ConfigLoader::load();
					
		return new Bootstrap($config);
	}
	
} else {

	trigger_error('This application requires PHP 5.1+. Please upgrade your version of PHP.', E_USER_ERROR);
	
}
?>