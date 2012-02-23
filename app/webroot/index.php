<?php

// define the name of our app instance
// leave this blank if root instance, i.e. app
// not running in a subdirectory
$folder = explode( "/", dirname(__FILE__) );
$app_name = $folder[ (count($folder) - 2) ];
define('LOADING_SECTION', ucwords( $app_name ));

// set execution start time
define('EXECUTION_START', microtime());

// Here are our application file path definitions. Unless
// you're varying from the default install, these should not
// need to be changed.

define('BASE_PATH', str_replace("/". $app_name . "/webroot", "", dirname(__FILE__)) );

// define the file path to our system directory
if(file_exists(BASE_PATH . '/system')){
	define('SYSTEM_PATH', BASE_PATH . '/system');
} else {
	define('SYSTEM_PATH', str_replace(strtolower(LOADING_SECTION), '', BASE_PATH . 'system'));
}

// define the file path to our root application directory
define('APPLICATION_PATH', str_replace(DIRECTORY_SEPARATOR . "system", '', SYSTEM_PATH)); // path to the entire application root
define('MODULES_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules');
define('PLUGINS_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'plugins');

// here we'll quicly check for absolute minimal php5 support
if(version_compare(phpversion(), "5.3.0", 'ge')){

	// include the bootstrap file
	include(SYSTEM_PATH . DIRECTORY_SEPARATOR . 'bootstrap.php');

	// quickly, we need to run any pre-bootstrap plugin hooks

		// pull a list of plugins
		$plugins = Bootstrap::parsePluginRegistries();

		// load plugins with hooks we're calling next
		Bootstrap::callPluginHook('before_bootstrap_execute', $plugins);

	// load our config files
	include(SYSTEM_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ConfigLoader.php');
	$config = new ConfigLoader();

	// load the application system class
	if(!class_exists('App')){
		require(SYSTEM_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'App.php');
	}

	// create an instance of our entire app
	$application = new App($config);

} else {
	trigger_error('This application requires PHP 5.3+. Please upgrade your version of PHP.', E_USER_ERROR);
}
?>