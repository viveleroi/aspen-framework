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
class GroupsModel extends Model {

	/**
	 * We must allow the parent constructor to run properly
	 * @param string $table
	 * @access public
	 */
	public function __construct($table = false,$db = false){ parent::__construct($table,$db); }


	/**
	 * Validates the database table input
	 * @param array $fields
	 * @param string $primary_key
	 * @return boolean
	 */
	public function validate($fields = false, $primary_key = false){

		$clean = parent::pre_validate($fields, $primary_key);

		if($clean->isEmpty('name')){
			$this->addError('name', text('db:error:groupname'));
		}

		return $fields;

	}
}
?>