<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0.1-54
 */

/**
 * This class manages our mysql sql query generation
 * @package Aspen_Framework
 */
class ConfigModel extends Model {
	
	
	/**
	 * We must allow the parent constructor to run properly
	 * @param string $table
	 */
	public function __construct($table = false){ parent::__construct($table); }
	
	
	/**
	 * Ensures that a config key has been set before ANY updates allowed
	 * @param array $fields
	 * @param string $type insert or update
	 * @return boolean
	 */
	public function validate($fields = false, $type = false){
		
		$clean = parent::validate($fields, $type);
		
		if($clean->isEmpty('config_key')){
			$this->addError('config_key', 'The configuration key may not be empty.');
		}
		
		return !$this->error();
		
	}
}
?>