<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	Modules.Base
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Displays the front-end welcome page.
 * @package Aspen_Framework
 * @uses Bootstrap
 */
class Index extends Bootstrap {
	
	/**
	 * @abstract Constructor, initializes the module
	 * @return Index
	 * @param array $config Configuration settings passed through loader
	 * @access public
	 */
	public function __construct($config){ parent::__construct($config); }
	
	
	/**
	 * @abstract Redirects user to installation process if not installed
	 * @access public
	 */
	public function redirectOnInstall(){
		if(!$this->isInstalled()){
			header("Location: admin/");
			exit;
		}
	}
}
?>