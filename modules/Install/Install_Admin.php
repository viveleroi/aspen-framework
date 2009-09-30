<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	Modules.Base
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Handles installtion of our application.
 * @package Aspen_Framework
 * @uses Module
 */
class Install_Admin extends Module {


	/**
	 * Runs prerequisites check, if good sends user to config setup
	 * @access public
	 */
	public function view(){
		$this->APP->install->beginInstallProcess();
	}
	
	
	/**
	 * Runs prerequisites check, if good sends user to config setup
	 * @access public
	 */
	public function prereq(){
		$this->APP->install->prereq();
	}


	/**
	 * Users sets up their database / config file here
	 * @access public
	 */
	public function setup($retry = false){
		$this->APP->install->setup($retry);
	}
	
	
	/**
	 * Display config contents for creating files
	 * @access public
	 */
	public function paste_config($config){
		$this->APP->install->paste_config($config);
	}


	/**
	 * User creates the basic account at this point
	 * @access public
	 */
	public function account(){
		$this->APP->install->account();
	}
	
	
	/**
	 * Displays our installation success page
	 * @access public
	 */
	public function success(){
		$this->APP->install->success();
	}
	
	
	/**
	 * Displays a message that a database update is required
	 * @access public
	 */
	public function upgrade(){
		$this->APP->install->upgrade();
	}
	
	
	/**
	 * Processes the actual database upgrade
	 * @access public
	 */
	public function run_upgrade(){
		$this->APP->install->run_upgrade();
	}
	
	
	/**
	 * Registers a module (inserts a modules guid into the modules table)
	 * @param string $guid
	 */
	public function install_module($guid = false){
		$this->APP->install->install_module($guid);
	}
	
	
	/**
	 * Uninstalls a module (inserts a modules guid into the modules table)
	 * @param string $guid
	 */
	public function uninstall_module($guid = false){
		$this->APP->install->uninstall_module($guid);
		$this->APP->router->redirect('view', false, 'settings');
	}
}
?>