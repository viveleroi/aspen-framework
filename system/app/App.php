<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @version    	$Revision: 461 $
 * @since 		1.0
 * @revision 	$Id: bootstrap.php 461 2009-04-02 04:56:02Z mbotsko $
 */

/**
 * @abstract Runs the interface application. Loads the appropriate controllers for each module.
 * @package Aspen_Framework
 * @uses Bootstrap
 */
class App extends Bootstrap {

	/**
	 * @var array $_admin_menu Holds an array of links needed for the menu
	 * @access private
	 */
	private $_admin_menu;

	/**
	 * @var array $_modules_awaiting_install Contains a list of modules found on the server, but not yet installed
	 * @access private
	 */
	private $_modules_awaiting_install = false;


	/**
	 * @abstract Initializes the entire admin app
	 * @access public
	 */
	public function __construct($config){

		// call the bootstrap function to initialize everything
		parent::__construct($config);

		// generate a list of modules on the server awaiting install
		$this->listModulesAwaitingInstall();

		// if new display settings are set in the url, save them to the database
		if($this->params->get->getRaw('sort_location') && $this->params->get->getRaw('sort_by')){
			$this->prefs->addSort(
				$this->params->get->getRaw('sort_location'),
				$this->params->get->getRaw('sort_by'),
				$this->params->get->getRaw('sort_direction'));
		}

		// load all default user preferences
		if($this->params->session->getInt('user_id')){
			$this->prefs->loadUserPreferences();
		}
		
		// if logging required, set some defines
		if($this->requireLogin()){

			// determine if the current user is an admin
			define('IS_ADMIN', $this->user->userHasGlobalAccess());
	
			// determine if this a one-user system or not
			define('MULTIPLE_USERS', $this->user->userAccountCount() == 1 ? false : true);
			
		}

		// set the referring page unless we're POSTing
		$post_set = $this->params->getRawSource('post');
		if(!$post_set){
			$this->router->setReturnToReferrer();
		}

		// load a module based off of parameters in the URL
		$this->router->loadFromUrl();

		// end logging
		$this->log->write('Application request completed at ' . date("Y-m-d H:i:s"));
		
	}
	
	
	/**
	 * @abstract [DEPRECATED] Generates an unordered list nav admin menu based off of all links currently in the array. Aliases generateInterfaceMenu
	 * @param boolean $display_ul Whether or not to print out the UL element wrapper
	 * @return string
	 * @access private
	 */
	public function generateAdminMenu($display_ul = true){
		return $this->generateInterfaceMenu($display_ul);
	}

	
	/**
	 * @abstract Generates an unordered list nav admin menu based off of all links currently in the array.
	 * @param boolean $display_ul Whether or not to print out the UL element wrapper
	 * @return string
	 * @access public
	 */
	public function generateInterfaceMenu($display_ul = true){

		$menu = $display_ul ? '<ul id="primary_navigation">' : '';

		foreach($this->getInstalledModuleGuids() as $module){

			$reg = $this->moduleRegistry($module);

			if(is_object($reg)){
				if(isset($reg->disable_menu) && $reg->disable_menu){
				} else {
					
					$link = $this->template->createLink($reg->name, 'view', false, $reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false));
					
					if(!empty($link)){
						$menu .= '<li'.($this->router->here($reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false)) ? ' class="at"' : '').'>';
						$menu .= $link;
						$menu .= '</li>';
					}
				}
			}
		}

		$menu = $display_ul ? $menu .= '</ul>' : $menu;

		return $menu;

	}
	
	
	/**
	 * @abstract Generates menu items for non-base modules
	 * @return string
	 * @access public
	 */
	public function generateNonBaseModuleLinks(){
		
		$nonbase = $this->modules->getNonBaseModules();
		$menu = '';

		foreach($nonbase as $guid){

			$reg = $this->moduleRegistry($guid);

			if(is_object($reg)){
				if(isset($reg->disable_menu) && $reg->disable_menu){
				} else {
					
					$link = $this->template->createLink($reg->name, 'view', false, $reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false));
					
					if(!empty($link)){
						$menu .= '<li'.($this->router->here($reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false)) ? ' class="at"' : '').'>';
						$menu .= $link;
						$menu .= '</li>';
					}
				}
			}
		}

		return $menu;

	}
	

	/**
	 * @abstract Scans the modules installed on the server that are awaiting install
	 * @access private
	 */
	private function listModulesAwaitingInstall(){

		$awaiting = array();

		foreach($this->getModuleRegistry() as $module){

			// if the guid is not in the list of installed modules, set it as awaiting
			if(!in_array((string)$module->guid, $this->getInstalledModuleGuids()) && (string)$module->classname != "Install"){
				$awaiting[] = (array)$module;
			}
		}

		$this->_modules_awaiting_install = $awaiting;

	}


	/**
	 * @abstract Returns an array of all modules awaiting install
	 * @return array
	 * @access public
	 */
	public function getModulesAwaitingInstall(){
		return $this->_modules_awaiting_install;
	}


	/**
	 * @abstract Displays a box listing the modules awaiting installation, with links
	 * @access public
	 */
	public function modulesAwaitingInstallBox(){
		
		if(
			$this->user->isLoggedIn() &&
			is_array($this->_modules_awaiting_install) &&
			count($this->_modules_awaiting_install) > 0){

			$html = '<div class="notice"><em>'.$this->template->text('app:mod-install-intro').':</em><ul id="install-mod-box">'."\n";

			foreach($this->_modules_awaiting_install as $module){

				 $html .= sprintf('<li>%s %s</li>',
				 					$module['name'],
				 					$this->template->createLink(
				 										'Click to Install',
				 										'install_module',
				 										array('guid' => $module['guid']),
				 										'Install'))."\n";

			}

			$html .= '</ul></div>'."\n";

			print $html;

		}
	}
	
	
	/**
	 * @abstract Displays a box listing the modules awaiting installation, with links
	 * @access public
	 */
	public function modulesAwaitingInstallAlert(){
		
		if($this->router->getSelectedModule() != 'Settings_Admin' && $this->router->getSelectedModule() != 'Install_Admin'){
		
			if(
				$this->user->isLoggedIn() &&
				is_array($this->_modules_awaiting_install) &&
				count($this->_modules_awaiting_install) > 0){
	
				$html = '<div class="notice"><em>';
				$html .= $this->template->text('app:mod-install-intro');
				$html .= ' ' . $this->template->createLink('Click to View', 'view', false, 'Settings') . '</em></div>'."\n";
	
				print $html;
	
			}
		}
	}
	
	
	/**
	 * @abstract Displays a box listing the modules awaiting installation, with links
	 * @access public
	 */
	public function moduleControls(){
		
		$nonbase = $this->modules->getAllNonBaseModules();
		$items = array();

		foreach($nonbase as $guid){
		
			$reg = $this->moduleRegistry($guid);

			if(is_object($reg)){
				
				// status may be set to:
				// enabled (installed, and enabled)
				// disabled (installed, but disabled)
				// false (not installed)
				
				$status = false;

				$install_link = false;
				if(in_array((string)$reg->guid, $this->getInstalledModuleGuids())){
					//$install_link = $this->template->createLink('Disable', 'disable_module', array('guid' => $reg->guid), 'Install')."\n";
					
					// if disbaled
					//$install_link = $this->template->createLink('Enable', 'enable_module', array('guid' => $reg->guid), 'Install')."\n";
				} else {
					$install_link = true;
				}
				
				// any uninstall link
				$uninstall_link = false;
				if($status){
					$uninstall_link = true;
				}
				 					
				$items[] = array('name' => $reg->name, 'guid' => $reg->guid, 'install' => $install_link);

			}
		}


		return $items;

	}
	
	
	/**
	 * @abstract Displays a box listing all non-base modules that are either awaiting install or that may be uninstalled, with links to perform the actions.
	 * @access public
	 */
	public function modulesControlBox(){
		
		$controls = $this->moduleControls();
		$html = '<table><thead><tr><th>Module</th><th>Action</th></tr></thead><tbody>' . "\n";
		
		if(is_array($controls) && count($controls)){
			foreach($controls as $control){
				
				if($control['install']){
					$link = $this->template->createLink('Install', 'install_module', array('guid' => $control['guid']), 'Install')."\n";
				} else {
					$link = $this->template->createLink('Uninstall', 'uninstall_module', array('guid' => $control['guid']), 'Install')."\n";
				}
				
				$html .= sprintf('<tr><td>%s</td><td>%s</td></tr>' . "\n", $control['name'], $link);
				
			}
		} else {
			$html .= '<tr><td colspan="2">There are no modules to be installed or uninstalled.</td></tr>' . "\n";
		}
		
		$html .= '</tbody></table>';

		print $html;

	}
}
?>