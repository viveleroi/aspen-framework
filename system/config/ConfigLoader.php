<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2012 Trellis Development, LLC
 * @since 		2.0
 */

require( dirname(__FILE__) . DS . 'Config.php' );
require( dirname(__FILE__) . DS . 'UserConfigurable.php' );

/**
 * Description of Config
 *
 * @author botskonet
 */
class ConfigLoader {
	
	/**
	 * Holds the config object
	 * @var type 
	 */
	protected $config;
	
	
	/**
	 * 
	 */
	public function __construct(){
		
		$config = new Config();
		
		// Load base configurations, add to config
		$base_config = $this->_loadDefaultConfig();
		foreach($base_config as $key => $val){
			$config->set( $key, $val );
		}
		
		// Load Application Base Config
		
		
		
		
		// then try to load the user config file
		$config_path = $this->__getUserConfigPath();
		if($this->checkUserConfigExists($config_path)){

			require_once($config_path);
			if(class_exists('UserConfig')){
				// Pass config object to userconfig
				$uc = new UserConfig( $config, $config->determineHostname() );
				$uc = $uc->getObject();
				define('USER_CONFIG_LOADED', true);
				$this->config = $uc;
			}
		}
		
		$this->config =  $config;
		
	}
	
	
	/**
	 *
	 * @return type 
	 */
	public function getObject(){
		return $this->config;
	}
	
	
	/**
	 *
	 * @return boolean 
	 */
	protected function _loadDefaultConfig(){
		$config = false;
		include('config.default.php');
		return $config;
	}
	
	
	/**
	 *
	 * @return boolean 
	 */
	protected function _loadAppDefaultConfig(){

		$config = false;
		
		if(!defined('APP_CONFIG_PATH')){
			define('APP_CONFIG_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . "app.default.config.php");
		}
		if(file_exists(APP_CONFIG_PATH)){
			include(APP_CONFIG_PATH);
		}

		return $config;

	}
	
	
	/**
	 * Verifies whether or not the user config file exists
	 * @param string $config_path Path to configuration location
	 * @return boolean
	 * @access public
	 */
	public function checkUserConfigExists($config_path = false){
		return file_exists($config_path);
	}
	
	
	/**
	 *
	 * @param string $config_path
	 * @return string 
	 */
	protected function __getUserConfigPath( $config_path = false ){
		if(!$config_path){
			// set user config file location, using config prefix if set by server
			// (allows multiple "instances" of single install)
			if(!defined('CONFIG_PREFIX')){ define('CONFIG_PREFIX', ''); }
			$config_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . CONFIG_PREFIX . 'config.php';

		}
		return $config_path;
	}
}