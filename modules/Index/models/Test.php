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
class TestModel extends Model {
	
	public function __construct($table = false){
		parent::__construct($table);
	}
	
	public function results(){
		return 'it worked';
	}
}
?>