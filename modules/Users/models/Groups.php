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
class GroupsModel extends Model {

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
		if($clean->isEmpty('name')){
			$this->addError('name', $this->APP->template->text('db:error:groupname'));
		}

		return !$this->error();

	}
}
?>