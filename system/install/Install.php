<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Handles application and module installation
 * @package Aspen_Framework
 */
class Install extends Library {

	/**
	 * @var boolean $supported Whether or not the server is supported
	 * @access private
	 */
	private $supported;


	/**
	 * Constructor, initializes the module
	 * @return Install
	 * @access public
	 */
	public function __construct(){
		parent::__construct();
		$this->checkSystemCompatibility();
	}


	/**
	 * Runs a quick system prerequisite/versions check.
	 * @access private
	 */
	private function checkSystemCompatibility(){

		$supported = extension_loaded('mysql');
		$supported = $supported ? version_compare(PHP_VERSION, app()->config('minimum_version_php'), '>=') : false;

		$this->supported = $supported;

	}


	/**
	 * Returns whether or not the system prereq check passed
	 * @return public
	 */
	public function isSupported(){
		return $this->supported;
	}


	/**
	 * Runs prerequisites check, if good sends user to config setup or account creation
	 * @access public
	 */
	public function prereq(){

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'index.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display();

	}


	/**
	 * Runs prerequisites check, if good sends user to config setup or account creation
	 * @access public
	 */
	public function beginInstallProcess(){

		$_SESSION = array();
		session_destroy();

		if(!$this->isSupported()){

			app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
			app()->template->addView(app()->template->getModuleTemplateDir().DS . 'index.tpl.php');
			app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
			app()->template->display();

		} else {

			// if the config file exists, proceed to creating an account
			if(app()->checkUserConfigExists()){
				app()->router->redirect('account');
			} else {
				app()->router->redirect('setup');
			}
		}
	}


	/**
	 * Users sets up their database / config file here
	 * @access public
	 */
	public function setup($retry = false){

		// define the form
		$form = new Form();
		$form->addFields(array('db_username', 'db_password', 'db_database', 'db_hostname'));

		// process the form if submitted
		if($form->isSubmitted('post', 'submit')){

			$values = $form->getCurrentValues();

			// validation
			if($values->isEmpty('db_username')){
				$form->addError('db_username', 'You must enter a username');
			}

			if($values->isEmpty('db_database')){
				$form->addError('db_database', 'You must enter a database name.');
			}

			if($values->isEmpty('db_hostname')){
				$form->addError('db_hostname', 'You must enter a hostname or ip address.');
			}


			// if no error, proceed with setting up config file
			if(!$form->error()){

				// save the config to a file
				$fill = "<?php\n";
				$fill .= '$config[\'db_hostname\'] = \''.	$form->cv('db_hostname')	."';\n";
				$fill .= '$config[\'db_database\'] = \''.	$form->cv('db_database')	."';\n";
				$fill .= '$config[\'db_username\'] = \''.	$form->cv('db_username')	."';\n";
				$fill .= '$config[\'db_password\'] = \''.	$form->cv('db_password')	."';\n";
				$fill .= '?>';

				// check if we can write the config file ourselves
				if(touch(APPLICATION_PATH . DS . 'config.php')){

					app()->file->useFile(APPLICATION_PATH . DS . 'config.php');
					if(!app()->file->write($fill, 'w')){

						$this->paste_config($fill);
						exit;

					}
				} else {

					$this->paste_config($fill);
					exit;

				}
			}
		}

		// if the config file exists and db connection works, send on
		if(!app()->checkUserConfigExists() || $retry){

			app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
			app()->template->addView(app()->template->getModuleTemplateDir().DS . 'setup_config.tpl.php');
			app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
			app()->template->display(array('form'=>$form));

		} else {

			app()->router->redirect('account');

		}
	}


	/**
	 * Display config contents for creating files
	 * @access public
	 */
	public function paste_config($config){

		// check if config file exists
		if(!app()->checkUserConfigExists()){
			app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
			app()->template->addView(app()->template->getModuleTemplateDir().DS . 'paste_config.tpl.php');
			app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
			app()->template->display(array('config' => $config));
		} else {
			app()->router->redirect('account');
		}
	}


	/**
	 * User creates the basic account at this point
	 * @access public
	 */
	public function account(){

		// If no config file present we cannot proceed - sends user back to setup config
		if(!app()->checkUserConfigExists()){
			app()->sml->say('We were unable to find a configuration file. Please try again.');
			app()->router->redirect('setup', array('retry' => 'retry') );
		}

		// if the config exists and we're not creating an account, attempt to install the base tables
		if(!app()->params->post->keyExists('submit')){
			if(app()->db){

				// if no tables exist yet
				if(!count(app()->db->MetaTables('TABLES'))){
					// attempt to install our base tables
					if(!$this->installBaseTables()){
						unlink('../config.php');
						app()->sml->say('There was an error installing database tables. Please try again.');
						app()->router->redirect('setup', array('retry' => 'retry') );
					}
				}
			} else {

				app()->sml->say('We were unable to connect to the database using your current configuration. Please try again.');
				app()->router->redirect('setup', array('retry' => 'retry') );

			}
		}

		$form = new Form('users');
		$form->addField('password_2');

		// process the form if submitted
		if($form->isSubmitted('post', 'submit')){

			$values = $form->getCurrentValues();

			if(!$values->match('password', 'password_2')){
				$form->addError('password', 'Your passwords must match.');
			}

			if($form->save()){

				$group_link = app()->model->open('user_group_link');
				$group_link->insert( array('group_id'=>1,'user_id'=>1) );

				app()->router->redirect('success');
			}
		}

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'create_account.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display(array('form'=>$form));

	}


	/**
	 * Displays our installation success page
	 * @access public
	 */
	public function success(){

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'success.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display();

	}


	/**
	 * Runs the sql needed for installation of the basic app
	 * @access private
	 */
	private function installBaseTables(){

		$sql = array();

		$sql_path = app()->router->getModulePath() . DS . 'sql' . DS. 'install.sql.php';
		// include file with all install queries
		if(file_exists($sql_path)){
			include($sql_path);
		}

		$success = false;

		foreach($sql as $query){
			$success = app()->model->query($query);
		}

		if($success){
			$this->recordCurrentBuild();
		}

		return $success;

	}


	/**
	 * Records the current build in the upgrade history table
	 * @access private
	 */
	private function recordCurrentBuild(){
		app()->settings->setConfig( 'app.database.version', app()->formatVersionNumber(app()->config('application_version')) );
	}


	/**
	 * Displays a message that a database update is required
	 * @access public
	 */
	public function upgrade(){

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'upgrade.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display();

	}


	/**
	 * Processes the actual database upgrade
	 * @access public
	 */
	public function run_upgrade(){

		$sql_path = app()->router->getModulePath() . DS . 'sql' . DS. 'upgrade.sql.php';
		$success = false;

		// include file with all upgrade queries
		if(file_exists($sql_path)){
			include($sql_path);
		}

		if(isset($sql) && is_array($sql)){

			$my_old_build = app()->latestVersion();

			foreach($sql as $query_build => $queries){

				// the query build is after my old build, then apply the upgrade
				if(app()->versionCompare($query_build, $my_old_build) == 'greater'){

					// if array, run all queries
					if(isset($queries) && is_array($queries)){
						foreach($queries as $query){
							$success = app()->model->query($query);
						}
					}
				}
			}

			// update the upgrade history
			if($success){

				$this->recordCurrentBuild();

				app()->sml->say('Your database has been upgraded.');
				app()->router->redirect('view', false, app()->config('default_module'));

			}
		}

		app()->sml->say('No upgrade actions were performed.');
		app()->router->redirect('view', false, app()->config('default_module'));

	}


	/**
	 * Registers a module (inserts a modules guid into the modules table)
	 * @param string $guid
	 * @access public
	 */
	public function install_module($guid){

		$modules = app()->getModulesAwaitingInstall();
		foreach($modules as $module){
			if($guid == $module['guid']){

				$module_db = app()->model->open('modules');

				if($module_db->insert(array('guid' => $guid))){

					// refresh installed module guid list
					app()->listModules();

					// load the module and run the insert code
					$tmp_reg = app()->moduleRegistry($guid);

					if(isset($tmp_reg->classname)){

						$classname = $tmp_reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false);
						app()->loadModule($guid);

						// call module install process if it exists
						if(isset(app()->{$classname}) && method_exists(app()->{$classname}, 'install')){
							app()->{$classname}->install($guid);
						}

						app()->sml->say('The ' . $tmp_reg->classname . ' module has been installed successfully.');

					}
				}
			}
		}

		app()->router->returnToReferrer();

	}


	/**
	 * Used to run uninstallation code from module
	 * @param string $guid
	 * @access public
	 */
	public function uninstall_module($guid){

		// load the module and run the uninstall code
		$tmp_reg = app()->moduleRegistry($guid);

		if(isset($tmp_reg->classname)){
			$classname = $tmp_reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false);
			app()->loadModule($guid);

			// call module uninstall function if available
			if(method_exists(app()->{$classname}, 'uninstall')){
				app()->{$classname}->uninstall($guid);
			}

			// remove all connections from databases
			app()->model->query('DELETE FROM modules WHERE guid = "'.$guid.'"');
			app()->model->query('DELETE FROM permissions WHERE module = "'.$tmp_reg->classname.'"');

			app()->sml->say('The ' . $tmp_reg->classname . ' module has been uninstalled successfully.');

		}

		app()->router->returnToReferrer();

	}
}
?>