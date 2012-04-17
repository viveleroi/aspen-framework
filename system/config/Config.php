<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2012 Trellis Development, LLC
 * @since 		2.0
 */

/**
 * Description of Config
 *
 * @author botskonet
 */
class Config {
	
	/**
	 * @var array Holds internal config key=>values 
	 */
	protected $_config = array();
	
	
	/**
	 * Returns the raw array.
	 * @return type 
	 */
	public function _getConfigArray(){
		return $this->_config;
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
	
	
	// @todo implement
	public function extend(){
		
	}
	
	
	/**
	 *
	 * @return type 
	 */
	public function determineHostname(){
	
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