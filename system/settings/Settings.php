<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @version    	$Revision: 461 $
 * @since 		1.0
 * @revision 	$Id: bootstrap.php 461 2009-04-02 04:56:02Z mbotsko $
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

			$this->APP->model->select('config');
			$this->APP->model->where('config_key', $key);
			$config = $this->APP->model->results();
	
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

		if($key){
			$rec = $this->APP->model->quickSelectSingle('config', $key, 'config_key');
			if(is_array($rec)){
				$this->APP->model->executeUpdate('config', array('current_value'=>$value), $key, 'config_key');
			} else {
				$this->APP->model->executeInsert('config', array('current_value'=>$value,'config_key'=>$key));
			}
		}
	}
}
?>