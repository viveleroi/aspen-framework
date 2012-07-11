<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Runs the interface application. Loads the appropriate controllers for each module.
 * @package Aspen_Framework
 * @uses Bootstrap
 */
class App extends Bootstrap {


	/**
	 * Initializes the entire admin app
	 * @access public
	 */
	public function __construct($config){

		// call the bootstrap function to initialize everything
		parent::__construct($config);

		// if new display settings are set in the url, save them to the database
		if($this->params->get->getElemId('sort_location') && $this->params->get->getElemId('sort_by')){
			$this->prefs->addSort(
				$this->params->get->getElemId('sort_location'),
				$this->params->get->getElemId('sort_by'),
				$this->params->get->getElemId('sort_direction'));
		}

		if(config()->get('enable_authentication_support')){
			
			// determine user's authentication status
			$this->user->determineUserAuthentication();

			// load all default user preferences
			if($this->params->session->getInt('user_id')){
				$this->prefs->loadUserPreferences();
			}

			// determine if the current user is an admin
			define('IS_ADMIN', $this->user->userHasGlobalAccess());

			// determine if this a one-user system or not
			define('MULTIPLE_USERS', $this->user->userAccountCount() == 1 ? false : true);

		} else {
			define('IS_ADMIN', false);
			define('MULTIPLE_USERS', false);
		}

		// set the referring page unless we're POSTing
		$post_set = $this->params->getRawSource('post');
		if(!$post_set){
			$this->router->setReturnToReferrer();
		}

		// load a module based off of parameters in the URL
		$this->router->loadFromUrl();

		// end logging
		$end = Date::microtime();
		$this->log->write('Application request completed at ' . Date::formatMicrotime($end));
		$this->log->write('Time Spent: ' . ($end-Date::microtime(EXECUTION_START)).' seconds');

	}


	/**
	 * Generates an unordered list nav admin menu based off of all links currently in the array.
	 * @param boolean $display_ul Whether or not to print out the UL element wrapper
	 * @return string
	 * @access public
	 */
	public function generateInterfaceMenu($display_ul = true){
		$menu = $display_ul ? '<ul id="primary_navigation">' : '';
		foreach($this->getModuleRegistry() as $reg){
			if(is_object($reg)){
				if(isset($reg->disable_menu) && $reg->disable_menu){
				} else {
					$p = $this->template->getNamespacePath('view', $reg->classname, LS);
					$link = $this->template->link($reg->name, $p);
					if(!empty($link)){
						$menu .= '<li'.($this->router->here($p) ? ' class="at"' : '').'>';
						$menu .= $link;
						$menu .= '</li>';
					}
				}
			}
		}
		return ($display_ul ? $menu .= '</ul>' : $menu);
	}
}
?>