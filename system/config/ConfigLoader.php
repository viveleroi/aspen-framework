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
require( dirname(__FILE__) . DS . 'AppConfigurable.php' );

/**
 * Description of Config
 *
 * @author botskonet
 */
class ConfigLoader {
	
	
	/**
	 * 
	 */
	public static function load(){
		
		$config = new Config();
		
		// Load base configurations, add to config
		$base_config = self::_loadDefaultConfig();
		foreach($base_config as $key => $val){
			$config->set( $key, $val );
		}
		
		// Load Application Base Config
		$config = self::_loadAppDefaultConfig( $config );
		
		// then try to load the user config file
		$config_path = self::_getUserConfigPath();
		if(self::checkUserConfigExists( $config_path )){
			return self::_loadUserConfig ($config_path, $config );
		}
		
		return $config;
		
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
	protected function _loadAppDefaultConfig( $config ){

		if(!defined('APP_CONFIG_PATH')){
			define('APP_CONFIG_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . "app.default.config.php");
		}
		if(file_exists(APP_CONFIG_PATH)){
			require_once(APP_CONFIG_PATH);
			if(class_exists('AppConfig')){
				// Pass config object to userconfig
				$ac = AppConfig::load( $config );
				define('APP_CONFIG_LOADED', true);
				return $ac;
			}
		}

		return $config;

	}
	
	
	/**
	 * Verifies whether or not the user config file exists
	 * @param string $config_path Path to configuration location
	 * @return boolean
	 * @access public
	 */
	public static function checkUserConfigExists($config_path = false){
		return file_exists($config_path);
	}
	
	
	/**
	 *
	 * @param string $config_path
	 * @return string 
	 */
	protected function _getUserConfigPath( $config_path = false ){
		if(!$config_path){
			// set user config file location, using config prefix if set by server
			// (allows multiple "instances" of single install)
			if(!defined('CONFIG_PREFIX')){ define('CONFIG_PREFIX', ''); }
			$config_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . CONFIG_PREFIX . 'config.php';

		}
		return $config_path;
	}
	
	
	/**
	 * 
	 */
	protected function _loadUserConfig( $config_path, $config ){
		
		require_once($config_path);
		if(class_exists('UserConfig')){
			// Pass config object to userconfig
			$uc = new UserConfig( $config, $config->determineHostname() );
			$uc = $uc->getObject();
			define('USER_CONFIG_LOADED', true);
			return $uc;
		}
		return $config;
	}
}