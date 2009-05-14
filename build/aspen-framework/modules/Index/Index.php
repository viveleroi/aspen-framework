<?php

/**
 * @abstract Holds functions used by public pages
 * @package Aspen_Framework
 * @author Michael Botsko
 * @copyright 2008 Trellis Development, LLC
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