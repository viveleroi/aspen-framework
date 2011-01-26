<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0.1-4
 */

/**
 * Base parent class for application modules, sets up app reference
 * @package Aspen_Framework
 */
class Module {


	/**
	 * Loads our default dashboard screen
	 * @access public
	 */
	public function view(){
		template()->display();
	}


	/**
	 * Activates the default loading of the 404 error
	 */
	public function error_404(){
		router()->header_code(404);
		template()->setLayout('404');
		template()->display();
		exit;
	}

	
	/**
	 * Sets the template page title.
	 * @param string $str
	 */
	public function setPageTitle($str){
		template()->page_title = $str;
	}
}
?>