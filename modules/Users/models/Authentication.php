<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0.1-18
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
	 * @abstract Validates the database table input
	 * @param array $fields
	 * @param string $primary_key
	 * @return boolean
	 */
	public function validate($fields = false, $primary_key = false){

		$clean = parent::validate($fields, $primary_key);

		// verify username
		if($clean->isEmpty('username')){
			$this->addError('username', $this->APP->template->text('db:error:username'));
		}

		// validate empty pass
		if($clean->isEmpty('password')){
			$this->addError('password', $this->APP->template->text('db:error:password'));
		}

		// enforce sha1 on password
//		if($clean->isSetAndNotEmpty('password')){
//			$fields['password'] = sha1($clean->getRaw('password'));
//		}

		return !$this->error();

	}
}
?>