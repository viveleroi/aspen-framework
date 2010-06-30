<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	Modules.Base
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Handles forms for user preferences & settings
 * @package Aspen_Framework
 * @uses Module
 */
class Settings_Admin extends Module {


	/**
	 * Displays the settings page, including change password fields, etc
	 * @access public
	 */
	public function view(){

		// display the preferences form
		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'index.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display();

	}
}
?>