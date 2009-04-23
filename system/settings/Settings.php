<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Manages application settings
 * @package Aspen_Framework
 */
class Settings {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance(); }


	/**
	 * @abstract Returns a configuration value from the db
	 * @param string $key
	 * @return mixed
	 * @access public
	 */
	public function getConfig($key){

		$value = false;
		
		if($this->APP->checkDbConnection()){

			$cfg_model = $this->APP->model->open('config');
			$cfg_model->select('config');
			$cfg_model->where('config_key', $key);
			$config = $cfg_model->results();
	
			if($config['RECORDS']){
				foreach($config['RECORDS'] as $setting){
					$value = $setting['current_value'] == '' ? $setting['default_value'] : $setting['current_value'];
				}
			} else {
				$value = $this->APP->config($key);
			}
		}

		return $value;

	}


	/**
	 * @abstract Sets a configuration value
	 * @param string $key
	 * @param string $value
	 */
	public function setConfig($key = false, $value = false){
		
		$cfg_model = $this->APP->model->open('config');

		if($key){
			$rec = $cfg_model->quickSelectSingle('config', $key, 'config_key');
			if(is_array($rec)){
				$cfg_model->update(array('current_value'=>$value), $key, 'config_key');
			} else {
				$cfg_model->insert(array('current_value'=>$value,'config_key'=>$key));
			}
		}
	}
}
?>