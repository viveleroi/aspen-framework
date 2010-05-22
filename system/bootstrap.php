<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

// turn off the default error display
ini_set('display_errors', true);
error_reporting(E_ALL);

/**
 * define the framework revision
 * upon AF release, we set this during
 * the build process. We request you do not
 * change this value, so you can always
 * have a record of which AF revision
 * you're using.
 */
define('FRAMEWORK_REV', '');


/**
 * This base class provides a method allowing subclasses access to the higher object through reference.
 * @package Aspen_Framework
 * @access private
 */
class Base {
	private static $instance;
	public function Base(){ self::$instance =& $this; }
	public static function &get_instance(){ return self::$instance; }
}


/**
 * Returns an instance of our original app
 * @return object
 */
function &get_instance(){

	$APP = Base::get_instance();

	// set the timezone - we do this here so it's more global than
	// if it were called from bootstrap
	date_default_timezone_set($APP->config('timezone'));

	return $APP;

}


/**
 * Bootstrap, loads all of our configurations and required classes.
 * @package Aspen_Framework
 */
class Bootstrap extends Base {

	/**
	 * @var object $cache Holds the cache control object
	 * @access public
	 */
	public $cache = false;

	/**
	 * @var object $db Holds the database object
	 * @access public
	 */
	public $db = false;

	/**
	 * @var object $error Holds the error handler object
	 * @access public
	 */
	public $error = false;

	/**
	 * @var object $file Holds our file handling object
	 * @access public
	 */
	public $file = false;

	/**
	 * @var object $form Holds our form validation object
	 * @access public
	 */
	public $form = false;

	/**
	 * @var object $html Holds the HTMLPurifier object
	 * @access public
	 */
	public $html = false;

	/**
	 * @var object $install Holds our installer object
	 * @access public
	 */
	public $install = false;

	/**
	 * @var object $log System logging methods
	 * @access public
	 */
	public $log = false;

	/**
	 * @var object $mail Holds the mailer object
	 * @access public
	 */
	public $mail = false;

	/**
	 * @var object $model Holds the model object
	 * @access public
	 */
	public $model = false;

	/**
	 * @var object $modules Holds the module object
	 * @access public
	 */
	public $modules = false;

	/**
	 * @var object $params Holds the Inpeskt object
	 * @access public
	 */
	public $params = false;

	/**
	 * @var object $prefs Holds the preferences object
	 * @access public
	 */
	public $prefs = false;

	/**
	 * @var object $router Holds the router object
	 * @access public
	 */
	public $router = false;

	/**
	 * @var object $scaffold Holds the scaffolding object
	 * @access public
	 */
	public $scaffold = false;

	/**
	 * @var object $security Holds the security object
	 * @access public
	 */
	public $security = false;

	/**
	 * @var object $settings Holds the settings object
	 * @access public
	 */
	public $settings = false;

	/**
	 * @var object $sml Holds the session message log object
	 * @access public
	 */
	public $sml = false;

	/**
	 * @var object $template Holds the template object
	 * @access public
	 */
	public $template = false;

	/**
	 * @var object $thumbnail Holds the thumbail object
	 * @access public
	 */
	public $thumbnail = false;

	/**
	 * @var object $user Holds the user object
	 * @access public
	 */
	public $user = false;

	/**
	 * @var object $xml Holds the xml format object
	 * @access public
	 */
	public $xml = false;


	// PRIVATE VAR DEFINITIONS

	/**
	 * @var array $config Holds an array of all configuration settings
	 * @access private
	 */
	private $_config = false;

	/**
	 * @var array $config Holds an array of table child keys
	 * @access private
	 */
	private $_child_foreign_keys;

	/**
	 * @var array $config Holds an array of all successfully loaded libraries
	 * @access private
	 */
	private $_load_libraries = array();

	/**
	 * @var array Holds an array of all model class extensions
	 * @access private
	 */
	private $_model_extensions;

	/**
	 * @var array Holds an array of all model support libraries
	 * @access private
	 */
	private $_module_libraries;

	/**
	 * @var array $_modules Contains a list of modules found in the database.
	 * @access private
	 */
	private $_modules = false;

	/**
	 * @var array $_module_registry Holds data from the module registry files
	 * @access private
	 */
	private $_module_registry = false;

	/**
	 * @var array $plugins Holds data from the plugin registry files
	 * @access private
	 */
	private $_plugins;


	/**
	 * Constructor, loads configurations and required classes.
	 * The order in which these items are processed is very
	 * important - do not move items around.
	 *
	 * @access public
	 */
	public function __construct($config){

		// assign configuration data
		$this->_config = $config;
		$this->forceConfigValues();

		if(!defined('LOADING_SECTION')){
			define('LOADING_SECTION', '');
		}

		// set a few constants
		define('LS', strtolower(LOADING_SECTION));
		define('DS', DIRECTORY_SEPARATOR);
		define('REQUEST_START', date("YmdHis"));

		$interface = LS;
		if(is_array($this->config('interface_global_folder_replace'))){
			$replace = $this->config('interface_global_folder_replace');
			if(array_key_exists(LS, $replace)){
				$interface = $replace[LS];
			}
		}
		define('INTERFACE_PATH', APPLICATION_PATH . DS . strtolower($interface));

		if(!defined('INCLUDE_ONLY')){
			define('INCLUDE_ONLY', false);
		}

		// start the session
		$this->setSessionId();
		session_start();

		// load all plugins
		$this->_plugins = $this->parsePluginRegistries();

		// run the base class
		parent::Base();

		// check whether or not the config file exists
		// if not, route to default
		if(!Bootstrap::checkUserConfigExists()){
			$this->router->_selected_module = $this->config('default_module_no_config');
			$this->router->_selected_method = $this->config('default_method');
		}

		$this->setVersionConstants();

		// set monetary locale
		setlocale(LC_MONETARY, $this->config('currency_locale'));

		// load all of the module registry files into a local var
		$this->_module_registry = $this->parseModuleRegistries();

		// load in system libraries / classes
		$this->loadSystemLibraries();

		// generate a list of all available (installed) modules
		$this->listModules();

		// identify all model extensions
		$this->_model_extensions = $this->listModelExtensions();

		// load any model extensions
		$this->loadSystemModelExtensions();

		// enable system logging
		if($this->config('enable_logging')){
			$this->log->enable();
		}

		// enable cache
		if($this->config('enable_cache')){
			$this->cache->enable();
		}

		// throw a db error if the config exists, we're not installing, but the db connection fails
		if(!$this->db && $this->checkUserConfigExists() && $this->router->getSelectedModule() != "Install_Admin"){
			// This should only show if config exists but won't work
			$this->template->resetTemplateQueue();
	    	$this->template->addView($this->template->getTemplateDir().DS.'header.tpl.php');
			$this->template->addView($this->template->getTemplateDir().DS.'database-error.tpl.php');
			$this->template->addView($this->template->getTemplateDir().DS.'footer.tpl.php');
			$this->template->display();
			exit;
		} else {
			$this->log->write('Database connection is up and running.');
		}

		// Load the selected module and any dependencies unless the system is being included only
		if(!INCLUDE_ONLY){
			$this->loadCurrentModule();
		} else {
			$this->log->write('Skipping loading Application Interface module, INCLUDE_ONLY is true.');
		}
	}


	/**
	 * Allows sessions to be loaded by the session_id in the query string
	 * @access private
	 */
	private function setSessionId(){
		if($this->config('allow_session_id_from_query')){
			$sess_name = ini_get('session.name');
			$sess_id = isset($_GET[$sess_name]) ? $_GET[$sess_name] : false;
			$sess_id = trim(preg_replace('/[^A-Za-z0-9]/', '', $sess_id));
			if(!empty($sess_id)){
				session_id($sess_id);
			}
		}
	}


	/**
	 * Sets some constants based off our version/build info
	 * @access private
	 */
	private function setVersionConstants(){

		// update app version
		define('VERSION', $this->formatVersionNumber($this->config('application_version')));

		// update app build, if used
		define('BUILD', $this->formatVersionNumber($this->config('application_build'), true));

		// update app build, if used
		define('VERSION_COMPLETE', 'v'.VERSION.' b'.BUILD.' Aspen-'.FRAMEWORK_REV);
		
	}


	/**
	 * Determines whether or not the app has been installed
	 * @return boolean
	 * @access public
	 */
	public function isInstalled(){

		// check for user config
		$installed = Bootstrap::checkUserConfigExists();

		if($installed){
			if(isset($this->db) && is_object($this->db)){
				// attempt a query to see if tables installed
				$results = $this->db->Execute('SELECT * FROM modules');
				$installed = $results ? true : false;
				$installed = $installed ? $results->RecordCount() : false;
			} else {

				$installed = false;

			}
		}

		return (bool)$installed;

	}


	/**
	 * Loads the default config file
	 * @return array
	 * @access private
	 */
	static public function loadDefaultConfig(){

		$config = false;

		include(SYSTEM_PATH . DIRECTORY_SEPARATOR . 'config.default.php');

		if(!defined('APP_CONFIG_PATH')){
			define('APP_CONFIG_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . "app.default.config.php");
		}

		if(file_exists(APP_CONFIG_PATH)){
			include(APP_CONFIG_PATH);
		}

		return $config;

	}


	/**
	 * Verifies whether or not the user config file exists
	 * @param string $config_path Path to configuration location
	 * @return boolean
	 * @access public
	 */
	static public function checkUserConfigExists($config_path = false){

		if(!$config_path){

			// set user config file location, using config prefix if set by server
			// (allows multiple "instances" of single install)
			if(!defined('CONFIG_PREFIX')){ define('CONFIG_PREFIX', ''); }
			$config_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . CONFIG_PREFIX . 'config.php';

		}

		return file_exists($config_path) ? $config_path : false;

	}


	/**
	 * Loads all config files
	 * @return array
	 * @access private
	 */
	static public function loadAllConfigs(){

		// load the default first
		$all_config = Bootstrap::loadDefaultConfig();

		// then try to load the user config file
		if($config_path = Bootstrap::checkUserConfigExists()){

			include($config_path);

			// update our config with the user-set params
			if(isset($config) && is_array($config)){
				foreach($config as $param => $value){
					$all_config[$param] = $value;
				}
				define('USER_CONFIG_LOADED', true);
			}
		}

		return $all_config;

	}


	/**
	 * Forces values for config options if they conflict with environment settings
	 * @access private
	 */
	private function forceConfigValues(){

		// disable mod_rewrite if it's not present
		if(!$this->config('bypass_apache_modrewrite_check')){
			if(function_exists('apache_get_modules')){
				if(!in_array('mod_rewrite', apache_get_modules())){
					$this->_config['enable_mod_rewrite'] = false;
				}
			} else {
				$this->_config['enable_mod_rewrite'] = false;
			}
		}
	}


	/**
	 * Returns a configuration value from config files
	 * @param string $key
	 * @return mixed
	 * @access public
	 */
	public function config($key = false){
		if($key && isset($this->_config[$key])){
			return $this->_config[$key];
		}
		return false;
	}


	/**
	 * Returns the configuration array
	 * @return array
	 * @access public
	 */
	public function getConfig(){
		return $this->_config;
	}


	/**
	 * Sets a config value
	 * @param string $key
	 * @param mixed $value
	 * @access public
	 */
	public function setConfig($key, $value){
		$this->_config[$key] = $value;
	}


	/**
	 * Loads the core classes needed for AspenMSM
	 * @return bool
	 * @access private
	 */
	private function loadSystemLibraries(){

		$complete_load_success = false;

		// check if the config is loaded,
		// we can't connect to a db without it
		if(defined('USER_CONFIG_LOADED') && USER_CONFIG_LOADED){

			/**************
			 * Load the database class
			 */
			$system_class_array = array(array(
										'classname' => 'ADONewConnection',
										'folder' => 'adodb',
										'filename' => 'adodb.inc',
										'autoload' => false,
										'root' => SYSTEM_PATH));
			$this->loadSystemLibraryArray($system_class_array);

			$this->db = ADONewConnection($this->config('db_extension'));

			$this->db->SetFetchMode(ADODB_FETCH_ASSOC);

			if(!$this->db->Connect(
				$this->config('db_hostname'),
				$this->config('db_username'),
				$this->config('db_password'),
				$this->config('db_database'))) {

				$this->db = false;

			}
		} else {
			$this->db = false;
		}

		// attempt to map child foreign keys
		$this->_child_foreign_keys = $this->mapChildForeignKeys();

		// compile our final array of classes to load
		$all_classes 	= array();
		$base_classes 	= $this->config('load_core_class');
		$add_classes 	= $this->config('load_add_core_class');
		$module_classes = $this->listModelLibraries();

		// Merge user custom classes from config into base classes
		if(is_array($base_classes) && is_array($add_classes)){
			$base_classes = array_merge($base_classes, $add_classes);
		}

		// Merge module classes from register.xml into base classes
		if(is_array($base_classes) && is_array($module_classes)){
			$base_classes = array_merge($base_classes, $module_classes);
		}

		// Load all base system classes (defined in config.default.php
		if(is_array($base_classes)){
			foreach($base_classes as $class){
				$class['root'] = isset($class['root']) ? $class['root'] : SYSTEM_PATH;
				$all_classes[$class['classname']] = $class;
			}
		}

		// load all required modules
		if(is_array($all_classes) && count($all_classes) > 0){
			$complete_load_success = $this->loadSystemLibraryArray($all_classes);
		} else {
			print 'Invalid load_core_class list in configuration.';
			exit;
		}

		// specifically
		$this->error = new Error;
    	set_error_handler(array(&$this->error, 'raise'));

    	// assign supercage
    	$this->params->init();

		// load in permissions table
		$this->user->loadPermissions();

    	// router has been used already, so we need to force it to load
    	$this->router = new Router;

    	// set framework-related html purifier settings
    	if($this->isLibraryLoaded('HTMLPurifier')){
	    	$html_config = HTMLPurifier_Config::createDefault();
	    	if($this->config('enable_cache')){
	    		$html_config->set('Cache', 'SerializerPath', $this->config('cache_dir'));
	    	} else {
	    		$html_config->set('Cache', 'DefinitionImpl', null);
	    	}

	    	// set user-defined html purifier settings
	    	if(is_array($this->config('html_purifier_settings')) && count($this->config('html_purifier_settings')) > 0){
	    		foreach($this->config('html_purifier_settings') as $setting){
	    			$html_config->set($setting[0], $setting[1], $setting[2]);
	    		}
	    	}

	    	// load custom filters
	    	if(is_array($this->config('html_purifier_custom_filters'))){
	    		$classes = array();
	    		foreach($this->config('html_purifier_custom_filters') as $filter){
	    			include(SYSTEM_PATH.DS.'security'.DS.'Htmlpurifier'.DS.'standalone'.DS.'HTMLPurifier'.DS.'Filter/'.$filter['name'].'.php');
	    			$classes[] = new $filter['class'];
	    		}

		    	$html_config->set('Filter', 'Custom', $classes);

	    	}

	    	$this->html = new HTMLPurifier($html_config);
    	}

		// call any init functions - "constuct"-like functions but only
		// called once ALL libraries have been loaded.
		$this->runLibraryInits();

		// return load status
		return $complete_load_success;

	}


	/**
	 * Accepted values for load_core_class are:
	 * 'classname' => 'Security',
	 * 'folder' => false,
	 * 'filename' => false,
	 * 'var' => false,
	 * 'autoload' => false,
	 * 'extends' => 'childclassname'
	 * 'root' => '/full/path/to/root/of/class'
	 *
	 * Accepted values for custom_classes are:
	 * 'classname' => 'Myclass',
	 * 'root' => '/full/path/to/root/of/class',
	 * 'extends' => 'Settings'
	 *
	 * Accepts an array of system libraries to load
	 * @param array $library_array
	 * @return boolean
	 * @access private
	 */
	public function loadSystemLibraryArray($library_array){

		$load_success 	= true;
		$original_vars 	= array();

		if($load_success){
			foreach($library_array as $library){

				if(isset($library['classname'])){

					$folder 	= isset($library['folder']) ? $library['folder'] : strtolower($library['classname']);
					$filename 	= isset($library['filename']) ? $library['filename'] : $library['classname'];
					$var 		= isset($library['var']) ? $library['var'] : strtolower($library['classname']);
					$original_vars[$library['classname']] = $var;
					$autoload 	= isset($library['autoload']) ? $library['autoload'] : true;
					$extends 	= isset($library['extends']) ? $library['extends'] : false;
					$filebase 	= $library['root'] . DS . $folder;
					$filepath 	= $filebase . DS . $filename . '.php';

					if(!class_exists($library['classname'])){
						if(!include($filepath)){
							$this->error->raise(1, "Failed loading system library: " . $library, __FILE__, __LINE__);
							$load_success = false;
						} else {
							$this->_load_libraries[] = $library;
						}
					}

					// if extending a previous class, set variable to previous class,
					// but classname to extension
					if($extends){
						$var = isset($original_vars[$library['extends']]) ? $original_vars[$library['extends']] : false;
					}

					// if class included but no instance, load instance
					if($autoload && !empty($var)){
                        $this->{$var} = false;
                        if(class_exists($library['classname']) && !is_object($this->{$var})){
                            $this->{$var} = new $library['classname'];
                        }
                    }
				}
			}
		}

		return $load_success;

	}


	/**
	 * Calls any library init functions - "constuct"-like functions but only
	 * called once ALL libraries have been loaded.
	 *
	 * @access private
	 */
	private function runLibraryInits(){
		$libs = $this->getLoadedLibraries();
		foreach($libs as $lib){
			$obj_name = strtolower( isset($lib['var']) ? $lib['var'] : $lib['classname'] );
			if(isset($this->{$obj_name}) && is_object($this->{$obj_name})){
				if(method_exists($this->{$obj_name}, 'aspen_init')){
					$this->{$obj_name}->aspen_init();
				}
			}
		}
	}


	/**
	 * Returns the array of loaded system classes
	 * @return array
	 * @access public
	 */
	public function getLoadedLibraries(){
		return $this->_load_libraries;
	}


	/**
	 * Returns whether or not a library is loaded
	 * @param string $library
	 * @return boolean
	 * @access public
	 */
	public function isLibraryLoaded($library){
		foreach($this->_load_libraries as $lib){
			if($lib['classname'] == $library){
				return true;
			}
		}
		return false;
	}


	/**
	 * Returns the list of model extensions
	 * @return array
	 */
	public function getModelExtensions(){
		return $this->_model_extensions;
	}


	/**
	 * Generates a complete list of model extensions
	 * @return array
	 * @access private
	 */
	private function listModelExtensions(){

		$models = $this->config('models');
		$models = is_array($models) ? $models : array();

		// pull in any models defined in register.xml files
		$modules = $this->getModuleRegistry();
		foreach($modules as $module){
			if(isset($module->model) && is_object($module->model)){
				foreach($module->model as $table){
					$models[(string)$table] = array('module'=>(string)$module->folder);
				}
			}
		}

		return $models;

	}


	/**
	 * Generates a complete list of model extensions
	 * @return array
	 * @access private
	 */
	private function listModelLibraries(){

		$libs = array();

		// pull in any models defined in register.xml files
		$modules = $this->getModuleRegistry();
		if(is_array($modules)){
			foreach($modules as $module){
				if(isset($module->library) && is_object($module->library)){
					foreach($module->library as $lib){
						$libs[(string)$lib->classname] = array(
															'classname'=>(string)$lib->classname,
															'root' => MODULES_PATH.DS.$module->folder,
															'folder' => 'libs',
															'autoload' => (isset($lib->autoload) && $lib->autoload ? true : false),
															'extends' => (isset($lib->extends) && $lib->extends ? (string)$lib->extends : false)
														 );
					}
				}
			}
		}

		return $libs;

	}


	/**
	 * Attempts to map foreign keys to their parent table
	 * @return <type>
	 */
	private function mapChildForeignKeys(){

		$_db_schema = array();
		$_child_fks = array();

		if($this->checkDbConnection()){

			// iterate all tables in db
			$tables = $this->db->MetaTables();
			foreach($tables as $table){

				// pull complete schema for this table
				$_db_schema[$table] = $schema = $this->db->MetaColumns($table);

				// loop the schema and find the primary keys
				foreach($schema as $field => $field_info){
					if($field_info->primary_key){

						// convert the primary key to it's likely
						// foreign key name

						if(substr($table, -1) == 's'){
							$child_field = substr_replace($table ,"",-1);
						} else {
							$child_field = $table;
						}

						$_child_fks[$table] = $child_field.'_id';
					}
				}
			}
		}

		// now that we know what the child field name should be,
		// we need to look for it
		$map = array();

		foreach($_child_fks as $orig_table => $potent_field){
			foreach($_db_schema as $table => $schema){
				foreach($schema as $field => $field_info){
					if($potent_field == strtolower($field)){
						$map[$orig_table][$table] = $potent_field;
					}
				}
			}
		}

		return $map;

	}


	/**
	 * Returns the child key mappings
	 * @return array
	 * @access public
	 */
	public function getChildForeignKeys(){
		return $this->_child_foreign_keys;
	}


	/**
	 * Accepted values for "models" are:
	 * 'module' => 'Index',
	 * 'folder' => false,
	 * 'filename' => false,
	 * 'root' => '/full/path/to/root/of/class'
	 *
	 * Accepts an array of models to load
	 * @return boolean
	 * @access private
	 */

	public function loadSystemModelExtensions(){

		$load_success = true;

		$models = $this->_model_extensions;

		if($load_success){
			if(count($models)){
				foreach($models as $table => $model){
					if(is_array($model)){

						$module 	= isset($model['module']) ? $model['module'] : false;
						$folder 	= isset($model['folder']) ? $model['folder'] : 'models';
						$filebase 	= (isset($model['root']) ? $model['root'] : MODULES_PATH . DS . $module) . DS . $folder;
						$filename 	= isset($model['filename']) ? $model['filename'] : ucwords(strtolower($table));
						$filepath 	= $filebase . DS . $filename . '.php';

						if(!class_exists( ucwords($table).'Model' )){
							if(!include($filepath)){
								$this->error->raise(1, "Failed loading model extension: " . $table, __FILE__, __LINE__);
								$load_success = false;
							}
						}
					}
				}
			}
		}

		return $load_success;

	}


	/**
	 * Returns whether or not our db connection was made, and tables exist
	 * @return boolean
	 * @access public
	 */
	public function checkDbConnection(){
		if($this->db){
			if($this->db->Execute('SELECT * FROM modules LIMIT 1')){
				return true;
			}
		} else {
			return false;
		}
	}


	/**
	 * Scans the modules loaded on the server and loads their xml registration data
	 * @access private
	 */
	private function parseModuleRegistries(){

		$files = array();

		// open the folder
		$dir_handle = @opendir(MODULES_PATH);

		// loop through the files
		while ($file = readdir($dir_handle)) {

			if($file != "." && $file != ".."){

				// push the date folder into the array
				array_push($files, $file);

			}
		}

		// close
		closedir($dir_handle);

		// if files found, begin an array
		if(count($files) > 0){
			$module_registry = array();
		}

		// loop through each folder and look for a register.xml
		if(is_array($module_registry)){
			foreach($files as $file){

				$registry_path = MODULES_PATH . DS . $file . DS . 'register.xml';

				// ensure the file exists
				if(file_exists($registry_path)){
					$module_registry[$file] = simplexml_load_file($registry_path);
					$module_registry[$file]->folder = $file;
				}
			}
		}

		return $module_registry;

	}


	/**
	 * Scans the file system for any plugins
	 * @access private
	 */
	public function parsePluginRegistries(){

		//if($this->config('allow_plugins')){

			$files = array();

			$plugin_registry = false;

			// open the folder
			if(is_dir(PLUGINS_PATH)){
				$dir_handle = @opendir(PLUGINS_PATH);

				// loop through the files
				while ($file = readdir($dir_handle)) {

					if($file != "." && $file != ".."){

						// push the date folder into the array
						array_push($files, $file);

					}
				}

				// close
				closedir($dir_handle);
			}

			// if files found, begin an array
			if(count($files) > 0){
				$plugin_registry = array();
			}

			// loop through each folder and look for a register.xml
			if(is_array($plugin_registry)){
				foreach($files as $file){

					$registry_path = PLUGINS_PATH . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . 'register.xml';

					// ensure the file exists
					if(file_exists($registry_path)){
						$plugin_registry[$file] = simplexml_load_file($registry_path);
						$plugin_registry[$file]->folder = $file;
					}
				}
			}

			return $plugin_registry;

		//}
	}


	/**
	 * Calls plugins registered for any hooks
	 * @param string $hook_to_call
	 * @param array $plugins
	 * @access public
	 */
	public function callPluginHook($hook_to_call = false, $plugins = false){

		//if($this->config('allow_plugins')){

			// if plugins array coming from external source, use it
			// otherwise, try to use our own
			if(!$plugins){
				if(isset($this) && is_object($this)){
					$plugins = $this->_plugins;
				}
			}

			// if we have any, load them and call them
			if(is_array($plugins)){
				foreach($plugins as $plugin){
					if(isset($plugin->add_hook_func)){
						if($hook_to_call == $plugin->add_hook_func['hook']){

							$path = PLUGINS_PATH . DIRECTORY_SEPARATOR .
										$plugin->folder . DIRECTORY_SEPARATOR . $plugin->folder .'.php';

							// include the plugin
							if(file_exists($path)){

								include($path);

								// call the function
								$function = (string)$plugin->add_hook_func->function;
								$function();

							}
						}
					}
				}
			}
		//}
	}


	/**
	 * Returns a specific registry object for a selected module
	 * @param string $guid
	 * @param string $name
	 * @param string $interface
	 * @access public
	 * @return object
	 */
	public function moduleRegistry($guid = false, $name = false, $interface = false){

		if(isset($this) && is_object($this) && is_array($this->_module_registry)){
			$modules = $this->getModuleRegistry();
		} else {
			$modules = Bootstrap::parseModuleRegistries();
		}

		// loop the module registry
		foreach($modules as $module){

			// if ssn provided, look it up
			if($guid){
				if((string)$module->guid == (string)$guid){
					return $module;
				}

			}

			// if name provided
			if($name){

				$interface	= $interface ? $interface : LOADING_SECTION;
				$classname	= (string)$module->classname;
				$name		= $this->router->cleanModule($name);
				if($classname == $name){
					return $module;
				}
			}
		}
		return false;
	}


	/**
	 * Returns the module registry (xml object)
	 * @access public
	 */
	public function getModuleRegistry(){
		return $this->_module_registry;
	}


	/**
	 * Gathers a list of installed modules from db, stores it in local array.
	 * @return boolean
	 * @access private
	 */
	public function listModules(){
		if($this->checkDbConnection()){
			$model = $this->model->open('modules');
			$model->orderBy('sort_order');
			$modules = $model->results();
			if($modules){
				foreach($modules as $module){
					$this->_modules[] = $module['guid'];
				}
				return true;
			}
		}
		return false;
	}


	/**
	 * Returns the module list as an array
	 * @return array
	 * @access private
	 */
	public function getInstalledModuleGuids(){

		if(is_array($this->_modules)){
			return $this->_modules;
		} else {
			$mod = $this->moduleRegistry(false, $this->config('default_module_no_config'));
			if(isset($mod->guid)){
				return array((string)$mod->guid);
			}
		}
		return array();
	}


	/**
	 * Identifies the current module and it's prereqs to load
	 * @access private
	 */
	private function loadCurrentModule(){

		$current = $this->moduleRegistry(false, $this->router->getSelectedModule());

		if(is_object($current)){

			$modules_to_load = array($current->guid);

			if(isset($current->prerequisites->required)){
				foreach($current->prerequisites->required as $depend){
					array_push($modules_to_load, $depend->guid);
				}
			}

			foreach($modules_to_load as $module){

				$this->loadModule($module);

			}
			return true;
		}
		return false;
	}


	/**
	 * Loads a module
	 * @param string $module GUID of module to load
	 * @access private
	 * @return boolean
	 */
	public function loadModule($module){

		// load module
		$tmp_reg = $this->moduleRegistry($module);

		// assume this module is not allowed
		$allowed = false;

		if(isset($tmp_reg->classname)){

			if(in_array($tmp_reg->guid, $this->getInstalledModuleGuids())){

				// check the targetApp is set, if so we need to verify it
				if(isset($tmp_reg->targetApplication) && isset($tmp_reg->targetApplication->guid)){
					if($tmp_reg->targetApplication->guid == $this->config('application_guid')){

						$this->log->write('targetApplication set and matched for ' . $tmp_reg->classname . ' module.');

						// verify the target versions match
						if($this->registryVersionMatch($tmp_reg->targetApplication->minVersion, $tmp_reg->targetApplication->maxVersion)){
							$allowed = true;
							$this->log->write('Target application version requirements matched for ' . $tmp_reg->classname . ' module.');
						} else {
							$this->log->write('Target application versions failed to matched for ' . $tmp_reg->classname . ' module.');
							$this->error->raise(1, 'Target application versions failed to matched for ' . $tmp_reg->classname . ' module.', __FILE__, __LINE__);
						}

					} else {
						$this->log->write('Target application set but failed to matched for ' . $tmp_reg->classname . ' module.');
						$this->error->raise(1, 'Target application set but failed to matched for ' . $tmp_reg->classname . ' module.', __FILE__, __LINE__);
					}
				} else {
					$allowed = true;
					$this->log->write('No target application set for ' . (isset($tmp_reg->classname) ? $tmp_reg->classname : 'unknown') . ' module, IGNORING.');
				}
			} else {
				$this->error->raise(1, 'Attempting to load module ("' . (isset($tmp_reg->classname) ? $tmp_reg->classname : 'unknown') . '") which is not installed.', __FILE__, __LINE__);
			}


			// if we are allowed now
			if($allowed){

				$classname = $tmp_reg->classname . (LOADING_SECTION ? '_' . LOADING_SECTION : false);
				$file = MODULES_PATH . DS . $tmp_reg->folder . DS . $classname. '.php';

				if(file_exists($file)){
					include($file);
					$this->{$classname} = new $classname;
					$this->log->write('Loading Module: ' . $classname);

					// call any module hooks for this module
					$this->modules->callModuleHooks($tmp_reg->guid);
					$this->log->write('Calling module hooks for: ' . $classname);

				}
			} else {

				$this->error->raise(1, 'The module "' . (isset($tmp_reg->classname) ? $tmp_reg->classname : 'unknown') . '" is not compatible with this version of this application.', __FILE__, __LINE__);

			}
		} else {

			$this->error->raise(1, 'Failure to load module "'.$module.'" - classname was empty.', __FILE__, __LINE__);

		}
		return true;
	}


	/**
	 * Compares revision numbers to make sure target apps match
	 * @param string $min
	 * @param string $max
	 * @return boolean
	 * @access private
	 */
	private function registryVersionMatch($min, $max){

		$app_vers = $this->config('application_version');

		if(empty($app_vers)){
			$this->error->raise(1, 'Module requires a version check, but the application version is empty.', __FILE__, __LINE__);
		}

		if($this->versionCompare($min, $app_vers) != 'greater' && $this->versionCompare($max, $app_vers) != 'less'){
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Determines whether or not the database is awaiting an auto-upgrade
	 * @access public
	 */
	public function awaitingUpgrade(){
		if($this->config('watch_pending_db_upgrade') && $this->isInstalled()){

			$build = $this->formatVersionNumber($this->config('application_version'));
			$latest_build = $this->latestVersion();

			if($this->versionCompare($build, $latest_build) == 'greater'){
				$sql_path = MODULES_PATH . '/Install/sql/upgrade.sql.php';

				// include file with all upgrade queries
				if(file_exists($sql_path)){
					include($sql_path);
				}

				if(isset($sql) && is_array($sql)){
					foreach($sql as $new_build => $updates){
						if($this->versionCompare($new_build, $latest_build) == 'greater'){ return true; }
					}
				}
			}
			if($this->versionCompare($build, $latest_build) == 'less'){
				$this->error->raise(1, 'Your database appears to be from a version newer than your current installation.', __FILE__, __LINE__);
			}
		}
		return false;
	}


	/**
	 * Compared two version strings for similarity
	 * @param string $build
	 * @param string $match
	 * @return string
	 * @access public
	 */
	public function versionCompare($build, $match){

		$diff = false;

		$build = explode('.', $this->formatVersionNumber($build));
		$match = explode('.', $this->formatVersionNumber($match));

		// get full count so we know the largest array
		$fullcnt = count($build) > count($match) ? count($build) : count($match);

		for($i = 0; $i <= $fullcnt; $i++){

			$build_inc = isset($build[$i]) ? $build[$i] : 0;
			$match_inc = isset($match[$i]) ? $match[$i] : 0;

			if((int)$build_inc > $match_inc){
				$diff = 'greater';
			}
			elseif((int)$build_inc < $match_inc){
				$diff =  'less';
			}
			elseif((int)$build_inc == $match_inc){
				$diff =  'equal';
			} else {
			}

			if($diff != 'equal'){
				return $diff;
			}

		}
		return $diff;
	}


	/**
	 * formats a version number to match a 1.2.3.456 or similar type format
	 * @param string $build
	 * @return string
	 * @access private
	 */
	public function formatVersionNumber($build, $allow_text = false){

		$build = $allow_text ? $build : preg_replace('/[a-zA-Z ]/', '', $build);
		$build = explode('.', $build);

		$version = array();
		foreach($build as $key => $inc){
			if(strlen($inc) > 0){
				$version[] = ($allow_text ? $inc : (int)$inc);
			}
		}

		return implode('.', $version);

	}


	/**
	 * Returns the latest build number from the database
	 * @return mixed
	 * @access private
	 */
	public function latestVersion(){
		return $this->formatVersionNumber( $this->settings->getConfig('app.database.version') );
	}
}
?>