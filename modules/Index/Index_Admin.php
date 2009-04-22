<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	Modules.Base
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Displays the default dashboard/welcome screens
 * @package Aspen_Framework
 * @uses Module
 */
class Index_Admin extends Module {


	/**
	 * @abstract Loads our default dashboard screen
	 * @access public
	 */
	public function view(){
		
		$new_model = $this->APP->orm->factory('authentication');

		$new_model->select('authentication');
		
		print_r($new_model->results());
		
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();
		
	}
}
?>