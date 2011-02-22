<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	Modules.Base
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Displays the default dashboard/welcome screens
 * @package Aspen_Framework
 * @uses Module
 */
class Index_Admin extends Module {


	/**
	 * Loads our default dashboard screen
	 * @access public
	 */
	public function view(){
		template()->display();
	}
	
	
	public function test(){
		print 'hello';
	}
}
?>