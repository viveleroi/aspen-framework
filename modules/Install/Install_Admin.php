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
		app()->install->beginInstallProcess();
	}
	
	
	/**
	 * Runs prerequisites check, if good sends user to config setup
	 * @access public
	 */
	public function prereq(){
		app()->install->prereq();
	}


	/**
	 * Users sets up their database / config file here
	 * @access public
	 */
	public function setup($retry = false){
		app()->install->setup($retry);
	}
	
	
	/**
	 * Display config contents for creating files
	 * @access public
	 */
	public function paste_config($config){
		app()->install->paste_config($config);
	}


	/**
	 * User creates the basic account at this point
	 * @access public
	 */
	public function account(){
		app()->install->account();
	}
	
	
	/**
	 * Displays our installation success page
	 * @access public
	 */
	public function success(){
		app()->install->success();
	}
	
	
	/**
	 * Displays a message that a database update is required
	 * @access public
	 */
	public function upgrade(){
		app()->install->upgrade();
	}
	
	
	/**
	 * Processes the actual database upgrade
	 * @access public
	 */
	public function run_upgrade(){
		app()->install->run_upgrade();
	}
	
	
	/**
	 * Registers a module (inserts a modules guid into the modules table)
	 * @param string $guid
	 */
	public function install_module($guid = false){
		app()->install->install_module($guid);
	}
	
	
	/**
	 * Uninstalls a module (inserts a modules guid into the modules table)
	 * @param string $guid
	 */
	public function uninstall_module($guid = false){
		app()->install->uninstall_module($guid);
		app()->router->redirect('view', false, 'settings');
	}
}
?>