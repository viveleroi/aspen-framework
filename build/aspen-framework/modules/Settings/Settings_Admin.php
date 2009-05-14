<?php

/**
 * @abstract Handles forms for user preferences & settings
 * @package Aspen_Framework
 * @author Michael Botsko
 * @copyright 2008 Trellis Development, LLC
 */
class Settings_Admin {
	
	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor, initializes the module
	 * @return Settings_Admin
	 * @access public
	 */
	public function __construct(){ $this->APP = get_instance(); }
	

	/**
	 * @abstract Displays the settings page, including change password fields, etc
	 * @access public
	 */
	public function view(){

		// display the preferences form
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();

	}
}
?>