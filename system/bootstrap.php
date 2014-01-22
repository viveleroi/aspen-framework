<?php

/**
 * @package     Aspen_Framework
 * @subpackage  System
 * @author      Michael Botsko
 * @copyright   2009 Trellis Development, LLC
 * @since       1.0
 */

// turn off the default error display
ini_set('display_errors', false);
error_reporting(E_ALL);

/**
 * define the framework revision
 * upon AF release, we set this during
 * the build process. We request you do not
 * change this value, so you can always
 * have a record of which AF revision
 * you're using.
 */
define('FRAMEWORK_REV', 'Git-Version');


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
function &app(){
    $APP = Base::get_instance();
    // set the timezone - we do this here so it's more global than
    // if it were called from bootstrap
    date_default_timezone_set($APP->config->get('timezone'));
    return $APP;
}


/**
 * Shortcut to return an instance of session params
 * @return object
 */
function &session(){
    return app()->session;
}


/**
 * Shortcut to return an instance of session params
 * @return object
 */
function &post(){
    return app()->post;
}


/**
 * Shortcut to return an instance of session params
 * @return object
 */
function &get(){
    return app()->get;
}


/**
 * Shortcut to return an instance of session params
 * @return object
 */
function &server(){
    return app()->server;
}


/**
 * Shortcut to return an instance of session params
 * @return object
 */
function &cookie(){
    return app()->cookie;
}


/**
 * Shortcut to return an instance of session params
 * @return object
 */
function &config(){
    return app()->config;
}


/**
 * Bootstrap, loads all of our configurations and required classes.
 * @package Aspen_Framework
 */
class Bootstrap extends Base {

    /**
     * @var array $config Holds an array of all configuration settings
     * @access public
     */
    public $config = false;

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
     * @var object $params Holds the Peregrine object
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
     * @var object $user Holds the user object
     * @access public
     */
    public $user = false;


    /**********************************************
     * PUBLIC SUPERGLOBAL/PEREGRINE DEFINITIONS
     ****************************/

    /**
     * @var object Peregrine object for COOKIE superglobal
     * @access public
     */
    public $cookie;

    /**
     * @var object Peregrine object for ENV superglobal
     * @access public
     */
    public $env;

    /**
     * @var object Peregrine object for FILES superglobal
     * @access public
     */
    public $files;

    /**
     * @var object Peregrine object for GET superglobal
     * @access public
     */
    public $get;

    /**
     * @var object Peregrine object for POST superglobal
     * @access public
     */
    public $post;

    /**
     * @var object Peregrine object for SERVER superglobal
     * @access public
     */
    public $server;

    /**
     * @var object Peregrine object for SESSION superglobal
     * @access public
     */
    public $session;


    /**********************************************
     * PRIVATE VAR DEFINITIONS
     ****************************/

    /**
     * @var array $config Holds an array of table child keys
     * @access private
     */
    private $_db_schema;

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

        // Store configuration object
        $this->config = $config;

        if(!defined('LOADING_SECTION')){
            define('LOADING_SECTION', 'app');
        }

        // set a few constants
        define('LS', strtolower(LOADING_SECTION));

        $interface = LS;
        if(is_array($this->config->get('interface_global_folder_replace'))){
            $replace = $this->config->get('interface_global_folder_replace');
            if(array_key_exists(LS, $replace)){
                $interface = $replace[LS];
            }
        }
        define('INTERFACE_PATH', APPLICATION_PATH . DS . strtolower($interface));

        if(!defined('INCLUDE_ONLY')){
            define('INCLUDE_ONLY', false);
        }

        // start the session
        session_start();

        // load all plugins
        $this->_plugins = self::parsePluginRegistries();

        // run the base class
        parent::Base();

        // check whether or not the config file exists
        // if not, route to default
        if(!ConfigLoader::checkUserConfigExists()){
            $this->router->_selected_module = config()->get('default_module_no_config');
            $this->router->_selected_method = config()->get('default_method');
        }

        $this->setVersionConstants();

        // set monetary locale
        setlocale(LC_MONETARY, config()->get('currency_locale'));

        // load in system libraries / classes
        $this->loadSystemLibraries();

        // identify all model extensions
        $this->_model_extensions = $this->listModelExtensions();

        // load any model extensions
        $this->loadSystemModelExtensions();

        // enable system logging
        if(config()->get('enable_logging')){
            $this->log->enable();
        }

        // throw a db error if the config exists, we're not installing, but the db connection fails
        if(!$this->db && ConfigLoader::checkUserConfigExists() && $this->router->module() != "Install_Admin"){
            trigger_error('General database failure.', E_USER_ERROR);
            exit;
        } else {
            $this->log->write('Database connection is up and running.');
        }

        // Load the selected module and any dependencies unless the system is being included only
        if(!INCLUDE_ONLY){
            $this->loadRequestedController();
        } else {
            $this->log->write('Skipping loading Application Interface module, INCLUDE_ONLY is true.');
        }
    }


    /**
     * Returns the current configuration object in use.
     * @return type
     */
    public function getConfig(){
        return $this->config;
    }


    /**
     * Sets some constants based off our version/build info
     * @access private
     */
    private function setVersionConstants(){

        // update app version
        define('VERSION', $this->formatVersionNumber(config()->get('application_version')));

        // update app build, if used
        define('BUILD', $this->formatVersionNumber(config()->get('application_build'), true));

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
        $installed = ConfigLoader::checkUserConfigExists();

        if($installed){
            if(isset($this->db) && is_object($this->db)){
                $installed = $this->checkDbConnection();
            } else {
                $installed = false;
            }
        }

        return (bool)$installed;

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
        if(defined('USER_CONFIG_LOADED') && USER_CONFIG_LOADED && config()->get('db_enable')){

            /**************
             * Connect to the DB
             */
            $this->db = mysqli_connect(
                config()->get('db_hostname'),
                config()->get('db_username'),
                config()->get('db_password'),
                config()->get('db_database')
            ) or
                die("Problem connecting: " . mysqli_error($this->db));
        } else {
            $this->db = false;
        }

        // compile our final array of classes to load
        $all_classes    = array();
        $base_classes   = config()->get('load_core_class');
        $add_classes    = config()->get('load_add_core_class');
        $module_classes = $this->listApplicationLibraries();

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
        set_exception_handler(array(&$this->error, 'raiseException'));

        // assign supercage
        $this->params->init();
        // reassign the public vars to our own for easier access
        $this->post     = $this->params->post;
        $this->get      = $this->params->get;
        $this->session  = $this->params->session;
        $this->env      = $this->params->env;
        $this->files    = $this->params->files;
        $this->cookie   = $this->params->cookie;
        $this->server   = $this->params->server;

        // load database schema
        if($this->db){
            $this->_db_schema = $this->model->loadDatabaseSchema();
        }

        // load user perms
        if($this->isInstalled()){
            $this->user->loadPermissions();
        }

        // router has been used already, so we need to force it to load
        // determine if anything extends router
        $_router_class = 'Router';
        foreach($all_classes as $_class => $_tmp_c){
            if(isset($_tmp_c['extends']) && $_tmp_c['extends'] == 'Router'){
                $_router_class = $_class;
            }
        }
        $this->router = new $_router_class;

        // set framework-related html purifier settings
        if($this->isLibraryLoaded('HTMLPurifier')){
            $html_config = HTMLPurifier_Config::createDefault();
            if(config()->get('enable_cache')){
                $html_config->set('Cache.SerializerPath', config()->get('cache_dir'));
            } else {
                $html_config->set('Cache.DefinitionImpl', null);
            }

            // set user-defined html purifier settings
            if(is_array(config()->get('html_purifier_settings')) && count(config()->get('html_purifier_settings')) > 0){
                foreach(config()->get('html_purifier_settings') as $setting){
                    $html_config->set($setting[0].'.'.$setting[1], $setting[2]);
                }
            }

            // load custom filters
            if(is_array(config()->get('html_purifier_custom_filters'))){
                $classes = array();
                foreach(config()->get('html_purifier_custom_filters') as $filter){
                    include(SYSTEM_PATH.DS.'security'.DS.'Htmlpurifier'.DS.'standalone'.DS.'HTMLPurifier'.DS.'Filter/'.$filter['name'].'.php');
                    $classes[] = new $filter['class'];
                }

                $html_config->set('Filter.Custom', $classes);

            }

            // add new attributes
            $def = $html_config->getHTMLDefinition(true);
            if(is_array(config()->get('html_purifier_new_attributes')) && count(config()->get('html_purifier_new_attributes')) > 0){
                foreach(config()->get('html_purifier_new_attributes') as $attr){
                    $def->addAttribute($attr[0], $attr[1], $attr[2]);
                }
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

        $load_success   = true;
        $original_vars  = array();

        if($load_success){
            foreach($library_array as $library){

                if(isset($library['classname'])){

                    $filename   = isset($library['filename']) ? $library['filename'] : $library['classname'];
                    $var        = isset($library['var']) ? $library['var'] : strtolower($library['classname']);
                    $original_vars[$library['classname']] = $var;
                    $autoload   = isset($library['autoload']) ? $library['autoload'] : true;
                    $extends    = isset($library['extends']) ? $library['extends'] : false;
                    $folder     = (isset($library['folder']) ? DS.$library['folder'] : '' );
                    $filepath   = $library['root'] .$folder . DS . $filename . '.php';

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
     * Calls any library init functions - "construct"-like functions but only
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

        $models = config()->get('models');
        $models = is_array($models) ? $models : array();

        // open the folder
        if(is_dir(MODELS_PATH)){
            $dir_handle = @opendir(MODELS_PATH);
            while ($file = readdir($dir_handle)) {
                if($file != "." && $file != ".."){
                    $file = str_replace('.php', '', strtolower($file));
                    $models[$file] = array('root'=>MODELS_PATH,'filename'=>$file);
                }
            }
            closedir($dir_handle);
        }

        return $models;

    }


    /**
     * Generates a complete list of model extensions
     * @return array
     * @access private
     */
    private function listApplicationLibraries(){

        $libs = array();

        // open the folder
        if(is_dir(LIBS_PATH)){
            $dir_handle = @opendir(LIBS_PATH);
            while ($file = readdir($dir_handle)) {
                if($file != "." && $file != ".."){

                    $likelyClassName = str_replace('.php', '', $file);

                    $libs[$likelyClassName] = array(
                            'classname' => $likelyClassName,
                            'filename'=>$likelyClassName,
                            'root' => LIBS_PATH,
                            'autoload' => false,
                            'extends' => ""
                        );

                }
            }
            closedir($dir_handle);
        }

        return $libs;

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

        $models = $this->_model_extensions;

        if(count($models)){
            foreach($models as $table => $model){
                if(is_array($model)){

                    $filename   = isset($model['filename']) ? $model['filename'] : ucwords(strtolower($table));
                    $filepath   = $model['root'] . DS . $filename . '.php';

                    if(!class_exists( ucwords($table).'Model' )){
                        if(!include($filepath)){
                            $this->error->raise(1, "Failed loading model extension: " . $table, __FILE__, __LINE__);
                        }
                    }
                }
            }
        }
    }


    /**
     * Returns whether or not our db connection was made, and tables exist
     * @return boolean
     * @access public
     */
    public function checkDbConnection(){
        if($this->db){
            if(mysqli_query($this->db, "SELECT 1")){
                return true;
            }
        } else {
            return false;
        }
    }


    /**
     * Returns the database schema
     * @return array
     */
    public function getDatabaseSchema($table = false){
        if($table){
            return isset($this->_db_schema[$table]) ? $this->_db_schema[$table]  : false;
        } else {
            return $this->_db_schema;
        }
    }


    /**
     * Scans the file system for any plugins
     * @access private
     */
    public static function parsePluginRegistries(){

        $files = array();

        $plugin_registry = false;

        // open the folder
        if(is_dir(PLUGINS_PATH)){
            $dir_handle = @opendir(PLUGINS_PATH);
            while ($file = readdir($dir_handle)) {
                if($file != "." && $file != ".."){
                    array_push($files, $file);
                }
            }
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
                if(file_exists($registry_path)){
                    $plugin_registry[$file] = simplexml_load_file($registry_path);
                    $plugin_registry[$file]->folder = $file;
                }
            }
        }

        return $plugin_registry;
    }


    /**
     * Calls plugins registered for any hooks
     * @param string $hook_to_call
     * @param array $plugins
     * @access public
     */
    public static function callPluginHook($hook_to_call = false, $plugins = false){

        // if plugins array coming from external source, use it
        // otherwise, try to use our own
        if(!$plugins){
//          if(isset($this) && is_object($this)){
//              $plugins = $this->_plugins;
//          }
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
    }


    /**
     *
     */
    protected function loadRequestedController(){
        $map = $this->router->getMap();
        if( file_exists($map['load_path']) ){
            require_once($map['load_path']);
            $this->{$map['module']} = new $map['module'];
        }
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
     * Refreshes the cage of a superglobal and re-assigns that to the bootstrap
     * shortcut.
     * @param string $type
     */
    public function refreshCage($type){
        app()->params->refreshCage($type);
        if($type == 'session'){
            $this->{$type}  = $this->params->{$type};
        }
    }
}