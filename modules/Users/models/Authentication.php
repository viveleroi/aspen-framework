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
	 *
	 * @param <type> $fields
	 * @param <type> $type
	 * @return <type>
	 */
	public function validate($fields = false, $type = false){

		$clean = parent::validate($fields, $type);

		// verify username
		if($clean->isEmpty('username', $type)){
			$this->addError('username', $this->APP->template->text('db:error:username'));
		}

		// validate empty pass
		if($clean->isEmpty('password', $type)){
			$this->addError('password', $this->APP->template->text('db:error:password'));
		}

		// enforce sha1 on password
		if($clean->isSetAndNotEmpty('password')){
			$fields['password'] = sha1($fields['password']);
		}

		return !$this->error();

	}
}
?>