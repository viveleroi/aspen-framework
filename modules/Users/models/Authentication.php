<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract This class manages our mysql sql query generation
 * @package Aspen_Framework
 */
class AuthenticationModel extends Model {
	
	/**
	 * @abstract We must allow the parent constructor to run properly
	 * @param string $table
	 */
	public function __construct($table = false){ parent::__construct($table); }
	
	
	/**
	 * @abstract Only allow a user record to be added if a username is set
	 * @param array $fields
	 * @return mixed
	 */
	public function insert($fields = false){
		
		if(isset($fields['username']) && !empty($fields['username'])){
			return parent::insert($fields);
		}
		
		return false;
		
	}
}
?>