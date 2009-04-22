<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.1
 */

/**
 * @abstract 
 * @package Aspen_Framework
 */
class Orm {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor
	 * @return Modules
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance();  }
	
	
	public function factory($table = false){
		
		if(!$table){
			$orm_model = new Model;
			$orm_model->openTable($table);
			return $orm_model;
		}
	}
}
?>