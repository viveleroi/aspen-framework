<?php

/**
 * @abstract Handles installtion of our application.
 * @package Aspen_Framework
 * @author Michael Botsko
 * @copyright 2008 Trellis Development, LLC
 * @uses Install
 */
class Install_Admin {

	/**
	 * @var object $APP Allows access to the base application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor, initializes the module
	 * @access public
	 */
	public function __construct(){ $this->APP = get_instance(); }


	/**
	 * @abstract Runs prerequisites check, if good sends user to config setup
	 * @access public
	 */
	public function view(){
		$this->APP->install->beginInstallProcess();
	}
	
	
	/**
	 * @abstract Runs prerequisites check, if good sends user to config setup
	 * @access public
	 */
	public function prereq(){
		$this->APP->install->prereq();
	}


	/**
	 * @abstract Users sets up their database / config file here
	 * @access public
	 */
	public function setup($retry = false){
		$this->APP->install->setup($retry);
	}
	
	
	/**
	 * @abstract Display config contents for creating files
	 * @access public
	 */
	public function paste_config($config){
		$this->APP->install->paste_config($config);
	}


	/**
	 * @abstract User creates the basic account at this point
	 * @access public
	 */
	public function account(){
		$this->APP->install->account();
	}
	
	
	/**
	 * @abstract Displays our installation success page
	 * @access public
	 */
	public function success(){
		$this->APP->install->success();
	}
	
	
	/**
	 * @abstract Displays a message that a database update is required
	 * @access public
	 */
	public function upgrade(){
		$this->APP->install->upgrade();
	}
	
	
	/**
	 * @abstract Processes the actual database upgrade
	 * @access public
	 */
	public function run_upgrade(){
		$this->APP->install->run_upgrade();
	}
	
	
	/**
	 * @abstract Registers a module (inserts a modules guid into the modules table)
	 * @param string $guid
	 */
	public function install_module($guid = false){
		$this->APP->install->install_module($guid);
	}
	
	
	/**
	 * @abstract Uninstalls a module (inserts a modules guid into the modules table)
	 * @param string $guid
	 */
	public function uninstall_module($guid = false){
		$this->APP->install->uninstall_module($guid);
		$this->APP->router->redirect('view', false, 'settings');
	}
}
?>