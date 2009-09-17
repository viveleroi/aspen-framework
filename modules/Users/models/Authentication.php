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
	 * @access public
	 */
	public function __construct($table = false){ parent::__construct($table); }


	/**
	 * @abstract Validates the database table input
	 * @param array $fields
	 * @param string $primary_key
	 * @return boolean
	 * @access public
	 */
	public function validate($fields = false, $primary_key = false){

		$clean = parent::validate($fields, $primary_key);

		// verify username
		if($clean->isEmpty('username')){
			$this->addError('username', $this->APP->template->text('db:error:username'));
		} else {

			// if we're adding the record, check for existing username
			if(!$primary_key){
				$user = $this->open('authentication');
				$user->where('username', $clean->getRaw('username'));
				$unique = $user->results();
				if($unique['RECORDS']){
					$this->addError('username', $this->APP->template->text('db:error:username-dup'));
				}
			}
		}

		// if we're inserting new record, no empty pass
		if(!$primary_key && $clean->isEmpty('password')){
			$this->addError('password', $this->APP->template->text('db:error:password'));
		}

		return !$this->error();

	}


	/**
	 * @abstract Runs additional logic on the insert query
	 * @param array $fields
	 * @return mixed
	 * @access public
	 */
	public function insert($fields = false){

		// enforce a sha1 on the password
		if(array_key_exists('password', $fields) && !empty($fields['password'])){
			$fields['password'] = $this->encode_password($fields['password']);
		}

		// insert
		return parent::insert($fields);

	}


	/**
	 * @abstract Runs additional logic on the update query
	 * @param array $fields
	 * @return mixed
	 * @access public
	 */
	public function update($fields = false, $where_value = false, $where_field = false ){

		// if the password provided =, encode it - otherwise, remove
		if(!empty($fields['password'])){
			$fields['password'] = $this->encode_password($fields['password']);
		} else {
			unset($fields['password']);
		}

		return parent::update($fields, $where_value, $where_field);
	}


	/**
	 * @abstract Defines an encoding type for the password.
	 * @param string $pass
	 * @return string
	 * @access
	 * private
	 */
	protected function encode_password($pass = false){
		if(!empty($pass)){
			return sha1($pass);
		}
	}
}
?>