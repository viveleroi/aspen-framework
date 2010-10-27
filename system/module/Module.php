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
	 * Constructor
	 */
	public function  __construct() {}


	/**
	 * Loads our default dashboard screen
	 * @access public
	 */
	public function view(){
		app()->template->display();
	}


	/**
	 * Activates the default loading of the 404 error
	 */
	public function error_404(){
		app()->router->header_code(404);
		app()->template->addView(app()->template->getTemplateDir().DS . '404.tpl.php');
		app()->template->display();
		exit;
	}

	
	/**
	 * Sets the template page title.
	 * @param string $str
	 */
	public function setPageTitle($str){
		app()->template->page_title = $str;
	}
}
?>