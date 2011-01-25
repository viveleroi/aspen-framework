<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0.1-18
 */

/**
 * This class manages our mysql sql query generation
 * @package Aspen_Framework
 */
class UsersModel extends Model {

	/**
	 * We must allow the parent constructor to run properly
	 * @param string $table
	 * @access public
	 */
	public function __construct($table = false){ parent::__construct($table); }


	/**
	 * Validates the database table input
	 * @param array $fields
	 * @param string $primary_key
	 * @return boolean
	 * @access public
	 */
	public function validate($fields = false, $primary_key = false){

		$clean = parent::validate($fields, $primary_key);

		if($clean->isEmpty('first_name')){
			$this->addError('first_name', text('db:error:first_name'));
		}

		if($clean->isEmpty('last_name')){
			$this->addError('last_name', text('db:error:last_name'));
		}

		// verify username
		if($clean->isEmpty('username')){
			$this->addError('username', text('db:error:username'));
		} else {

			// if we're adding the record, check for existing username
			if(!$primary_key){
				$user = $this->open('users');
				$user->where('username', $clean->getEmail('username'));
				$unique = $user->results();
				if($unique){
					$this->addError('username', text('db:error:username-dup'));
				}
			}
		}

		// verify password (unless both field are sent, password is ignored entirely)
		if($clean->keyExists('_raw_password') && $clean->keyExists('password_confirm')){
			if($clean->isSetAndEmpty('_raw_password') || $clean->isSetAndEmpty('password_confirm')){
				$this->addError('password', text('db:error:password'));
			} else {
				if(!$clean->match('_raw_password', 'password_confirm')){
					$this->addError('password', text('db:error:password_match'));
				}
			}
		}

		// verify groups
		if(!$clean->isEmpty('Groups')){
			// Make sure an admin isn't removing his own admin status
			if(defined('IS_ADMIN') && IS_ADMIN && $primary_key == session()->getInt('user_id')){
				if(!$clean->isInArray('Groups', 1)){
					$this->addError('Groups', text('db:error:groups-noadmin'));
				}
			}
		}

		// if we're inserting new record, no empty pass
		if(!$primary_key && $clean->isEmpty('password')){
			$this->addError('password', text('db:error:password'));
		}

		return !$this->error();

	}


	/**
	 * Runs additional logic on the insert query
	 * @param array $fields
	 * @return mixed
	 * @access public
	 */
	public function before_insert($fields = false){

		// enforce a sha1 on the password
		if(array_key_exists('password', $fields) && !empty($fields['password'])){
			$fields['_raw_password'] = $fields['password'];
			$fields['password'] = $this->stringHash($fields['password']);
		}

		// set date created
		$fields['date_created'] = gmdate(DATE_FORMAT);

		return $fields;

	}


	/**
	 * Runs additional logic on the update query
	 * @param array $fields
	 * @return mixed
	 * @access public
	 */
	public function before_update($fields = false){

		// if the password provided =, encode it - otherwise, remove
		if(!empty($fields['password']) && isset($fields['password_confirm'])){
			$fields['_raw_password'] = $fields['password'];
			$fields['password'] = $this->stringHash($fields['password']);
		} else {
			unset($fields['password']);
		}

		return $fields;

	}


	/**
	 * Returns a securely hashed string.
	 * @param <type> $pass
	 * @return <type>
	 */
	final private function stringHash($pass){
		$p = new PasswordHash();
		return $p->HashPassword($pass);
	}
}
?>