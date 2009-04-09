<?php

/**
 * @abstract Displays the default dashboard/welcome screens
 * @package Aspen_Framework
 * @author Michael Botsko
 * @copyright 2008 Trellis Development, LLC
 */
class Index_Admin {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor, initializes the module
	 * @return Index_Admin
	 * @access public
	 */
	public function __construct(){ $this->APP = get_instance(); }


	/**
	 * @abstract Loads our default dashboard screen
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