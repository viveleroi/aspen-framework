<?php

// define the name of our app instance
// leave this blank if root instance, i.e. app
// not running in a subdirectory
define('LOADING_SECTION', 'Admin');

// Here are our application file path definitions. Unless
// you're varying from the default install, these should not
// need to be changed.

// define the file path to our system directory
if(file_exists(dirname(__FILE__) . '/system')){
	define('SYSTEM_PATH', dirname(__FILE__) . '/system');
} else {
	define('SYSTEM_PATH', str_replace(strtolower(LOADING_SECTION), '', dirname(__FILE__) . 'system'));
}

// define the file path to our root application directory
define('APPLICATION_PATH', str_replace(DIRECTORY_SEPARATOR . "system", '', SYSTEM_PATH)); // path to the entire application root
define('MODULES_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules');
define('PLUGINS_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'plugins');

// here we'll quicly check for absolute minimal php5 support
if(version_compare(phpversion(), "5.1.0", 'ge')){

	// include the bootstrap file
	include(SYSTEM_PATH . DIRECTORY_SEPARATOR . 'bootstrap.php');

	// quickly, we need to run any pre-bootstrap plugin hooks

		// pull a list of plugins
		$plugins = Bootstrap::parsePluginRegistries();

		// load plugins with hooks we're calling next
		Bootstrap::callPluginHook('before_bootstrap_execute', $plugins);

	// load our config files
	$config = Bootstrap::loadAllConfigs();

	// load the application system class
	if(!class_exists('App')){
		require(SYSTEM_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'App.php');
	}

	// create an instance of our entire app
	$application = new App($config);

} else {

	trigger_error('This application requires PHP 5.1+. Please upgrade your version of PHP.', E_USER_ERROR);

}
?>