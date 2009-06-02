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
	
	
	public function validate($fields = false){
		
		$clean = parent::validate($fields);
		
		if(is_object($clean)){
			
			// validate username
			if($clean->isEmpty('username')){
				$this->addError('username', $this->APP->template->text('query:username'));
			}
			
			// validate password
			if($clean->isEmpty('password')){
				$this->addError('password', 'You must enter a valid password.');
			} else {
				if(strlen($clean->getAlnum('password')) != 28){
					$this->addError('password', 'It does not appear that the password has been encrypted properly.');
				}
			}
		}
		
		return !$this->error();
		
	}
}
?>