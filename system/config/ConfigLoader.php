<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Config
 *
 * @author botskonet
 */
class Config {
	
	/**
	 *
	 * @var type 
	 */
	private $_config = array();
	
	/**
	 * 
	 */
	public function __contruct(){
		
		$this->_load();
		
	}
	
	
	/**
	 *
	 * @param type $key 
	 */
	public function get( $key ){
		if(isset($this->_config[$key])){
			return $this->_config[$key];
		}
	}
	
	
	/**
	 *
	 * @param type $key
	 * @param type $value 
	 */
	public function set( $key, $value ){
		$this->_config[$key] = $value;
	}
	
	
	/**
	 * 
	 */
	protected function _load(){
		
		// Load the default config file
		$all_config = $this->_loadDefaultConfig();
		
		// then try to load the user config file
		$config_path = $this->__getUserConfigPath($config_path);
		if($this->checkUserConfigExists($config_path)){

			require_once($config_path);

			// update our config with the user-set params
			if(isset($config) && is_array($config)){
				foreach($config as $param => $value){
					$all_config[$param] = $value;
				}
				define('USER_CONFIG_LOADED', true);
			}
		}
		
		$this->_config = $all_config;
		
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
	
	
	/**
	 *
	 * @return boolean 
	 */
	protected function _loadDefaultConfig(){

		$config = false;

		include('config.default.php');

		if(!defined('APP_CONFIG_PATH')){
			define('APP_CONFIG_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . "app.default.config.php");
		}

		if(file_exists(APP_CONFIG_PATH)){
			include(APP_CONFIG_PATH);
		}

		return $config;

	}
	
	
	/**
	 *
	 * @return type 
	 */
	protected function _determineHostname(){
	
		// Determine which var we'll use to figure out where we are, because
		// SERVER_NAME isn't set when using anything from command line (i.e. cron)
		$Server = false;
		if(isset($_SERVER['SERVER_NAME'])){
			$Server = $_SERVER['SERVER_NAME'];
		} else {
			if(isset($_SERVER['HOSTNAME'])){
				$Server = $_SERVER['HOSTNAME'];
			} else {
				// attempt to gather the hostname from the network settings, usually
				// only when run from cli/cron
				preg_match('/HOSTNAME=(.*)/', file_get_contents('/etc/sysconfig/network'), $network);
				$hostname = explode("=", $network[0]);
				$Server = (isset($hostname[1]) ? $hostname[1] : false);
			}
		}
		return $Server;
	}
}

?>