<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Manages application settings
 * @package Aspen_Framework
 */
class Settings extends Library {

	/**
	 * @var array Holds the user settings configurations from the db
	 */
	private $settings = array();


	/**
	 * Constructor
	 */
	public function  aspen_init() {
		if(app()->checkDbConnection()){
			$this->loadSettings( app()->session->getInt('user_id') );
		}
	}


	/**
	 *
	 * @param <type> $user_id
	 */
	public function loadSettings($user_id){
		$cfg_model	= app()->model->open('config');
		$cfg_model->where('user_id', $user_id);
		$this->settings = $cfg_model->results();
	}


	/**
	 * Returns a configuration value from the db
	 * @param string $key
	 * @param integer $user_id
	 * @return mixed
	 * @access public
	 */
	public function getConfig($key, $user_id = NULL){
		if(app()->checkDbConnection()){
			$cfg = $this->configRecord($key, $user_id);
			if(is_array($cfg)){
				return $cfg['current_value'] === '' ? $cfg['default_value'] : $cfg['current_value'];
			} else {
				return app()->config($key);
			}
		}
		return NULL;
	}


	/**
	 * Sets a configuration value - updates if it exists otherwise insert.
	 * @param string $key
	 * @param string $value
	 * @param integer $user_id
	 */
	public function setConfig($key = false, $value = false, $user_id = NULL){
		$new_rc = array('current_value'=>$value,'config_key'=>$key,'user_id'=>$user_id);
		$cfg_model	= app()->model->open('config');
		$cfg = $this->configRecord($key, $user_id);
		return is_array($cfg) ? $cfg_model->update($new_rc, $cfg['id']) : $cfg_model->insert($new_rc);
	}


	/**
	 * Loads the core config record
	 * @param string $key
	 * @param integer $user_id
	 * @return array
	 * @access private
	 */
	private function configRecord($key, $user_id = NULL){
		if(is_array($this->settings)){
			foreach($this->settings as $setting){
				if($setting['config_key'] == $key && $setting['user_id'] == $user_id ){
					return $setting;
				}
			}
		}
		return NULL;
	}
}
?>