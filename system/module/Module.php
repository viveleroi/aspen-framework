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


	/**
	 * Loads our default dashboard screen
	 * @access public
	 */
	public function view(){

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();

	}
}
?>