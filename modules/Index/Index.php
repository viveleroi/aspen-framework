<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	Modules.Base
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Displays the front-end welcome page.
 * @package Aspen_Framework
 * @uses Bootstrap
 */
class Index extends Bootstrap {
	
	/**
	 * Constructor, initializes the module
	 * @return Index
	 * @param array $config Configuration settings passed through loader
	 * @access public
	 */
	public function __construct($config){ parent::__construct($config); }
	
	
	/**
	 * Redirects user to installation process if not installed
	 * @access public
	 */
	public function redirectOnInstall(){
		if(!$this->isInstalled()){
			$this->router->redirectToUrl( $this->router->interfaceUrl('admin') );
			// or
			// $this->router->redirect(false, false, 'admin');
		}
	}
}
?>