<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.1-beta-1-16
 */

/**
 * Base parent class for application libraries, sets up app reference
 * @package Aspen_Framework
 */
class Library {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	protected $APP;


	/**
	 * Constructor, initializes the module
	 * @return Index_Admin
	 * @access public
	 */
	public function __construct(){ $this->APP = get_instance(); }

}
?>