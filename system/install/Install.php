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
 * @abstract Handles application and module installation
 * @package Aspen_Framework
 */
class Install {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;
	
	/**
	 * @var boolean $supported Whether or not the server is supported
	 * @access private
	 */
	private $supported;
	

	/**
	 * @abstract Constructor, initializes the module
	 * @return Install
	 * @access public
	 */
	public function __construct(){
		$this->APP = get_instance();
		$this->checkSystemCompatibility();
	}
	
	
	/**
	 * @abstract Runs a quick system prerequisite/versions check.
	 * @access private
	 */
	private function checkSystemCompatibility(){
	
		$supported = extension_loaded('mysql');
		$supported = $supported ? version_compare(PHP_VERSION, $this->APP->config('minimum_version_php'), '>=') : false;
		 
		$this->supported = $supported;
		
	}
	
	
	/**
	 * @abstract Returns whether or not the system prereq check passed
	 * @return public
	 */
	public function isSupported(){
		return $this->supported;
	}

	
	/**
	 * @abstract Runs prerequisites check, if good sends user to config setup or account creation
	 * @access public
	 */
	public function prereq(){
		
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();
			
	}
	
	
	/**
	 * @abstract Runs prerequisites check, if good sends user to config setup or account creation
	 * @access public
	 */
	public function beginInstallProcess(){
		
		$_SESSION = array();
		session_destroy();
		
		if(!$this->isSupported()){

			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
			$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
			$this->APP->template->display();
			
		} else {
	
			// if the config file exists, proceed to creating an account
			if($this->APP->checkUserConfigExists()){
				$this->APP->router->redirect('account');
			} else {
				$this->APP->router->redirect('setup');
			}
		}
	}
	
	
	/**
	 * @abstract Users sets up their database / config file here
	 * @access public
	 */
	public function setup($retry = false){
		
		// define the form
		$this->APP->form->addFields(array('db_username', 'db_password', 'db_database', 'db_hostname'));

		// process the form if submitted
		if($this->APP->form->isSubmitted('post', 'submit')){

			// validation
			if(!$this->APP->form->isFilled('db_username')){
				$this->APP->form->addError('db_username', 'You must enter a username');
			}
			
			if(!$this->APP->form->isFilled('db_database')){
				$this->APP->form->addError('db_database', 'You must enter a database name.');
			}
			
			if(!$this->APP->form->isFilled('db_hostname')){
				$this->APP->form->addError('db_hostname', 'You must enter a hostname or ip address.');
			}
			

			// if no error, proceed with setting up config file
			if(!$this->APP->form->error()){
				
				// save the config to a file
				$fill = "<?php\n";
				$fill .= '$config[\'db_hostname\'] = \''.	$this->APP->form->cv('db_hostname')	."';\n";
				$fill .= '$config[\'db_database\'] = \''.	$this->APP->form->cv('db_database')	."';\n";
				$fill .= '$config[\'db_username\'] = \''.	$this->APP->form->cv('db_username')	."';\n";
				$fill .= '$config[\'db_password\'] = \''.	$this->APP->form->cv('db_password')	."';\n";
				$fill .= '?>';

				// check if we can write the config file ourselves
				if(touch(APPLICATION_PATH . DS . 'config.php')){
				
					$this->APP->file->useFile(APPLICATION_PATH . DS . 'config.php');
					if(!$this->APP->file->write($fill, 'w')){
					
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
		if(!$this->APP->checkUserConfigExists() || $retry){

			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
			$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'setup_config.tpl.php');
			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
			$this->APP->template->display();
			
		} else {
			
			$this->APP->router->redirect('account');
			
		}
	}
	
	
	/**
	 * @abstract Display config contents for creating files
	 * @access public
	 */
	public function paste_config($config){

		// check if config file exists
		if(!$this->APP->checkUserConfigExists()){
			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
			$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'paste_config.tpl.php');
			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
			$this->APP->template->display(array('config' => $config));
		} else {
			$this->APP->router->redirect('account');
		}
	}
	
	
	/**
	 * @abstract User creates the basic account at this point
	 * @access public
	 */
	public function account(){
		
		// If no config file present we cannot proceed - sends user back to setup config
		if(!$this->APP->checkUserConfigExists()){
			$this->APP->sml->addNewMessage('We were unable to find a configuration file. Please try again.');
			$this->APP->router->redirect('setup', array('retry' => 'retry') );
		}

		// if the config exists and we're not creating an account, attempt to install the base tables
		if(!$this->APP->form->isSubmitted('post', 'submit')){
			if($this->APP->db){
				
				// if no tables exist yet
				if(!count($this->APP->db->MetaTables('TABLES'))){
					// attempt to install our base tables
					if(!$this->installBaseTables()){
						unlink('../config.php');
						$this->APP->sml->addNewMessage('There was an error installing database tables. Please try again.');
						$this->APP->router->redirect('setup', array('retry' => 'retry') );
					}
				}
			} else {
			
				$this->APP->sml->addNewMessage('We were unable to connect to the database using your current configuration. Please try again.');
				$this->APP->router->redirect('setup', array('retry' => 'retry') );
			
			}
		}
		
		
		$this->APP->form->addFields(array('email', 'nice_name', 'password_1', 'password_2'));

		// process the form if submitted
		if($this->APP->form->isSubmitted('post', 'submit')){

			// validation
			if(!$this->APP->form->isEmail('email')){
				$this->APP->form->addError('email', 'You must enter a valid email address.');
			}
			
			if(!$this->APP->form->isFilled('password_1')){
				$this->APP->form->addError('password_1', 'You must enter a password.');
			}
			
			if(!$this->APP->form->fieldsMatch('password_1', 'password_2')){
				$this->APP->form->addError('password_1', 'Your passwords must match.');
			}

			if(!$this->APP->form->error()){

				// create account
				$account_sql_tmpl = 'INSERT INTO authentication (username, nice_name, password) VALUES ("%s", "%s", "%s")';
				$account_sql = sprintf($account_sql_tmpl,
											$this->APP->form->cv('email'),
											$this->APP->form->cv('nice_name'),
											sha1($this->APP->form->cv('password_1')));
			
				if($this->APP->model->query($account_sql)){
				
					$this->APP->router->redirect('success');
				
				} else {

					$this->APP->sml->addNewMessage('We were unable to create your account. Please try again.');
				
				}
			}
		}

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'create_account.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();

	}
	
	
	/**
	 * @abstract Displays our installation success page
	 * @access public
	 */
	public function success(){
		
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'success.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();
		
	}
	
	
	/**
	 * @abstract Runs the sql needed for installation of the basic app
	 * @access private
	 */
	private function installBaseTables(){
		
		$sql = array();
		
		$sql_path = $this->APP->router->getModulePath() . DS . 'sql' . DS. 'install.sql.php';
		// include file with all install queries
		if(file_exists($sql_path)){
			include($sql_path);
		}
		
		$success = false;
	
		foreach($sql as $query){
			$success = $this->APP->model->query($query);
		}
		
		if($success){
			$this->recordCurrentBuild();
		}
	
		return $success;
	
	}
	
	
	/**
	 * @abstract Records the current build in the upgrade history table
	 * @access private
	 */
	private function recordCurrentBuild(){
		$this->APP->model->executeInsert('upgrade_history', array('current_build' => $this->APP->config('application_build'), 'upgrade_completed' => date("Y-m-d H:i:s")));
	}
	
	
	/**
	 * @abstract Displays a message that a database update is required
	 * @access public
	 */
	public function upgrade(){
		
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'upgrade.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();
		
	}
	
	
	/**
	 * @abstract Processes the actual database upgrade
	 * @access public
	 */
	public function run_upgrade(){
		
		$sql_path = $this->APP->router->getModulePath() . DS . 'sql' . DS. 'upgrade.sql.php';
		$success = false;
		
		// include file with all upgrade queries
		if(file_exists($sql_path)){
			include($sql_path);
		}
		
		if(isset($sql) && is_array($sql)){
			
			$my_old_build = $this->APP->latestBuild();
			
			foreach($sql as $query_build => $queries){
				
				// the query build is after my old build, then apply the upgrade
				if((int)$my_old_build < (int)$query_build){
				
					// if array, run all queries
					if(isset($queries) && is_array($queries)){
						foreach($queries as $query){
							$success = $this->APP->model->query($query);
						}
					}
				}
			}
			
			// update the upgrade history
			if($success){
				
				$this->recordCurrentBuild();
				
				$this->APP->sml->addNewMessage('Your database has been upgraded.');
				$this->APP->router->redirect('view', false, $this->APP->config('default_module'));
				
			}
		}
		
		$this->APP->sml->addNewMessage('No upgrade actions were performed.');
		$this->APP->router->redirect('view', false, $this->APP->config('default_module'));
		
	}
	
	
	/**
	 * @abstract Registers a module (inserts a modules guid into the modules table)
	 * @param string $guid
	 * @access public
	 */
	public function install_module($guid){
		
		$modules = $this->APP->getModulesAwaitingInstall();
		foreach($modules as $module){
			if($guid == $module['guid']){
				if($this->APP->model->executeInsert('modules', array('guid' => $guid))){
					
					// refresh installed module guid list
					$this->APP->listModules();
					
					// load the module and run the insert code
					$tmp_reg = $this->APP->moduleRegistry($guid);

					if(isset($tmp_reg->classname)){
						$classname = $tmp_reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false);
						$this->APP->loadModule($guid);
						if(method_exists($this->APP->{$classname}, 'install')){
							if($this->APP->{$classname}->install($guid)){
								$this->APP->sml->addNewMessage('The ' . $tmp_reg->classname . ' module has been installed successfully.');
							} else {
								$this->APP->sml->addNewMessage('There was an error installing the module. Please try again.');
							}
						}
					}
				}
			}
		}
		
		$this->APP->router->returnToReferrer();
		
	}
	
	
	/**
	 * @abstract Used to run uninstallation code from module
	 * @param string $guid
	 * @access public
	 */
	public function uninstall_module($guid){
					
		// load the module and run the uninstall code
		$tmp_reg = $this->APP->moduleRegistry($guid);

		if(isset($tmp_reg->classname)){
			$classname = $tmp_reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false);
			$this->APP->loadModule($guid);
			if(method_exists($this->APP->{$classname}, 'uninstall')){
				if($this->APP->{$classname}->uninstall($guid)){
					
					// remove all connections from databases
					$this->APP->model->query('DELETE FROM modules WHERE guid = "'.$guid.'"');
					$this->APP->model->query('UPDATE modules SET autoload_with = "" WHERE autoload_with = "'.$guid.'"');
					$this->APP->model->query('DELETE FROM permissions WHERE module = "'.$tmp_reg->classname.'"');
					
					$this->APP->sml->addNewMessage('The ' . $tmp_reg->classname . ' module has been uninstalled successfully.');
					
				} else {
					$this->APP->sml->addNewMessage('There was an error uninstalling the module. Please try again.');
				}
			}
		}
		
		$this->APP->router->returnToReferrer();
		
	}
}
?>