<?php

/**
 * DO NOT EDIT THIS FILE, THESE ARE FRAMEWORK DEFAULTS ONLY
 * DO NOT EDIT THIS FILE, THESE ARE FRAMEWORK DEFAULTS ONLY
 * DO NOT EDIT THIS FILE, THESE ARE FRAMEWORK DEFAULTS ONLY
 *
 * Default configurations that your application will utilize must be set
 * in the app.default.config.php file.
 *
 * Installation-specific configurations must be set in the config.php file.
 * This file must be created either manually or by the install process.
 *
 * Please see:
 * http://docs.trellisdevelopment.com/index.php/Aspen:Config
 * For more information.
 *
 * Some of these configs are often defined in the app default config file,
 * but are here so that we capture all possible framework configuration settings.
 */


/**
 * APPLICATION IDENTIFICATION SETTINGS
 *
 * These help modules and plugins identify your
 * application. The name and version are also
 * used in error reports, QA tools, public display,
 * etc.
 */

	// the name of your website / application
	$config['application_name'] = '';
	
	// the current application master GUID
	$config['application_guid'] = '';
	
	// application version
	$config['application_version'] = '';
	
	// application url - set to false for auto-detect
	$config['application_url'] = false;


/**
 * DATABASE CONNECTION SETTINGS
 *
 * These provide connection information to the framework.
 * Currently, the framework only supports MySQL.
 *
 */

	// connection username
	$config['db_username'] = 'root';
	
	// connection password
	$config['db_password'] = '';
	
	// database to select (user must have rights to this db)
	$config['db_database'] = 'aspen';
	
	// hostname for db
	$config['db_hostname'] = 'localhost';
	
	// adodb db extension
	$config['db_extension'] = "mysql";
	
	
/**
 * LANGUAGE FILE SETTINGS
 *
 * These enable settings within the framework that allows
 * modules to use language files with translations available
 * for your template. The language files must be created manually.
 */

	// toggle language support
	$config['enable_languages'] = true;

	// the current language (if language support is enabled)
	$config['language'] = 'en_US';
	
	
/**
 * MODULE LOADING SETTINGS
 *
 * These set url handling and module load values.
 */

	// the current application url - (complete url to root folder of the app) only set to override auto-detect
	$config['application_url'] = false;
	
	// enable mod_rewrite support
	$config['enable_mod_rewrite'] = true;
	
	// bypass apache_get_modules function check for mod_rewrite
	$config['bypass_apache_modrewrite_check'] = false;

	// default module if none specific in URL
	$config['default_module'] = 'Index' . (LOADING_SECTION ? '_' . LOADING_SECTION : false);

	// default module if no user config exists
	$config['default_module_no_config'] = 'Install_Admin';
	
	// default method
	$config['default_method'] = 'view';

	// force a secure connection
	$config['force_https'] = false;
	
	// Allow forwarding to original url after login
	$config['post_login_redirect'] = true;
	
	// Path to static content (css, js, images, etc)
	// set only if different from interface url
	$config['static_content_path'] = false;
	
	
/**
 * SYSTEM-TO-PUBLIC EMAIL SETTINGS
 *
 * Controls email values for outgoing emails generated by
 * the system. These typically include emails being
 * sent to staff, users, etc.
 */

	// email from address
	$config['email_sender'] = 'noreply@' .
			(array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'yourdomain.com');
	
	// email from sender name
	$config['email_sender_name'] = 'noreply@' .
			(array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'yourdomain.com');
			
	// password reset email subject
	$config['password_reset_subject'] = (array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : '') . 'Password Reset Form';

	// password reset email body
	$config['password_reset_body'] = 'Hello,<br /><br />Your password has been reset to {new_pass}.';
	
/**
 * DEBUG AND ERROR HANDLING SETTINGS
 *
 * Controls how errors are handled.
 */

	// minimum severity of error to display
	$config['minimum_displayable_error'] = 1;

	// save errors to database
	$config['save_error_to_db'] = true;
	
	// send emails for error messages
	$config['send_error_emails'] = false;

	// who to send error emails to
	$config['error_email_recipient'] = 'webmaster@' .
			(array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : false);

	// error email from address
	$config['error_email_sender'] = 'errors@' .
			(array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'yourdomain.com');

	// error email from sender name
	$config['error_email_sender_name'] = 'errors@' .
			(array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'yourdomain.com');

	// minimum version of php required
	$config['minimum_version_php'] = '5.1.0';
	
	// log errors on 404s: missing modules, methods
	$config['log_error_on_404'] = true;
	

/**
 * DATA FORMATTING SETTINGS
 *
 * Controls how data is formatted by default for output. Currency, time, etc.
 */
	
	// default timezone
	$config['timezone'] = "America/Los_Angeles";
	
	// currency format
	// see http://us.php.net/money_format for more information
	$config['currency_format'] = '%n';
	
	// currency locale
	// see http://us.php.net/money_format for more information
	$config['currency_locale'] = 'en_US';
	
	
/**
 * FRAMEWORK FUNCTIONALITY TOGGLES
 *
 * Enables or disables framework functionality.
 */
	
	// enabled authentication support
	$config['enable_authentication_support'] = true;
	
	// directory (from application root) of system class extensions
	$config['custom_classes_directory'] = 'custom';
	
	// require token authorization when forms are submitted
	$config['require_form_token_auth'] = false;
	
	// session message wrapping html
	$config['sml_message_html'] = '<p class="notice">%s</p>';
	
	// form error message wrapping (wraps area of possibly multiple errors)
	$config['form_error_wrapping_html'] = '<ul class="error">%s</ul>';
	
	// form error message wrapping (wraps each message inside of holder)
	$config['form_error_line_html'] = "<li>%s</li>\n";
	
	// html purifier settings (must be array, as passed to $config->set('Namespace', 'Directive', $value);
	// All configuration settings available here: http://htmlpurifier.org/live/configdoc/plain.html
	$config['html_purifier_settings'] = array();
	//$config['html_purifier_settings'][] = array('HTML', 'Allowed', 'p,img[src,alt]'); // (EXAMPLE)
	
	// load custom filters, placed in HTMLPurifier/Filters directory
	$config['html_purifier_custom_filters'] = false;
	//$config['html_purifier_custom_filters'] = array( array('name' => 'Vimeo', 'class'=>"HTMLPurifier_Filter_Vimeo"));
	
	
	// allow for plugin support
	// toggles:
	// - directory scanning for plugins
	// - plugin loading/registration
	// - plugin hook calls
	$config['allow_plugins'] = false; // not functional
	
	// allow checking for database upgrades
	$config['watch_pending_db_upgrade'] = false;
	
	// mysql database field type grouping
	$config['mysql_field_group_dec']  = array('float', 'double', 'decimal');
	$config['mysql_field_group_int']  = array('tinyint', 'int', 'smallint', 'mediumint', 'bigint');
	$config['mysql_field_group_text'] = array('char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob');
	$config['mysql_field_group_date'] = array('date', 'datetime', 'timestamp', 'time', 'year');
	
	
/**
 *  FILE / UPLOADS SETTINGS
 *
 * Basic file handling settings, and file upload settings.
 */
	
	// allowed file extensions for file uploads and file handling
	$config['allowed_file_extensions'] = array('jpg', 'jpeg', 'png', 'gif');
	
	// enable uploads
	$config['enable_uploads'] = false;
	
	// the current file upload path - only set to override default
	$config['upload_server_path'] = str_replace("system", '', dirname(__FILE__)) . 'uploads';
	
	// path to upload files, via browser - only set to override default
	$config['upload_browser_path'] = false;
	
	// max file size of upload, in bytes
	$config['upload_max_file_size'] = 2000000;
	
	
/**
 *  SYSTEM LOG FILE SETTINGS
 *
 * Controls how general (non-error) system logs are
 * generated.
 */
	
	// enable logging output
	$config['enable_logging'] = false;
	
	// timestamp the log files
	$config['timestamp_log_file'] = false;
	
	// level of output sent to logs
		// 1 = all
		// 2 = All except constant, config, class output
		// 3 = All except constant, config, class output, and database queries
	$config['log_verbosity'] = 1;
	
	// logfile directory
	$config['log_dir'] = str_replace("system", '', dirname(__FILE__)) . 'logs';
	
	
/**
 *  CACHE DIRECTORY SETTINGS
 *
 * Controls how any cache files are stored
 */
	
	// enable cache features
	$config['enable_cache'] = false;
	
	// enable cache of template output
	$config['cache_template_output'] = false;
	
	// cache directory
	$config['cache_dir'] = str_replace("system", '', dirname(__FILE__)) . 'cache';

	
/**
 * LOADING CORE SYSTEM CLASSES
 *
 * The following classes are loaded by default and most are required for the
 * operation of the framework. You may extend these classes by providing an extension
 * class, following the documentation at:
 *
 * http://docs.aspen-framework.org/wiki/Aspen:Extending_System_Classes
 */
	
	$config['load_core_class'][] = array('classname' => 'Inspekt', 'folder' => 'security/Inspekt', 'autoload' => false);
	$config['load_core_class'][] = array('classname' => 'Security');
	$config['load_core_class'][] = array('classname' => 'Router', 'autoload' => false);
	$config['load_core_class'][] = array('classname' => 'Template');
	$config['load_core_class'][] = array('classname' => 'Error',
									'folder' => 'errors',
									'autoload' => false);
	$config['load_core_class'][] = array('classname' => 'File', 'folder' => 'files');
	$config['load_core_class'][] = array('classname' => 'Cache');
	$config['load_core_class'][] = array('classname' => 'HTMLPurifier',
									'folder' => 'security/Htmlpurifier',
									'var' => 'html',
									'autoload'=>false);
	$config['load_core_class'][] = array('classname' => 'Install');
	$config['load_core_class'][] = array('classname' => 'Log');
	$config['load_core_class'][] = array('classname' => 'Model');
	$config['load_core_class'][] = array('classname' => 'Module');
	$config['load_core_class'][] = array('classname' => 'Modules');
	$config['load_core_class'][] = array('classname' => 'PHPMailer', 'var' => 'mail');
	$config['load_core_class'][] = array('classname' => 'Settings');
	$config['load_core_class'][] = array('classname' => 'Sml');
	$config['load_core_class'][] = array('classname' => 'Form');
	$config['load_core_class'][] = array('classname' => 'User');
	$config['load_core_class'][] = array('classname' => 'Scaffold');
	
	// if user auth enabled - otherwise this is no use
	if($config['enable_authentication_support']){
		$config['load_core_class'][] = array('classname' => 'Preferences', 'var' => 'prefs');
	}

	
/**
 * LOADING ADDITIONAL SYSTEM CLASSES
 *
 * Some classes are not loaded by default since they are not required
 * for the framework to operate. The classes below have been provided
 * with the original distribution, and may be enabled by copying the
 * setting to your config files.
 *
 * You may also use this format for loading any additional classes you
 * are adding.
 */
	
	/*
	 
	// Xml encoding and output functionality
	$config['load_add_core_class'][] = array('classname' => 'Xml', 'folder' => 'formats');

	// RSS feed generation tool
	$config['load_add_core_class'][] = array(	'classname' => 'RSSBuilder',
											'folder' => 'formats/rss',
											'filename' => 'class.RSSBuilder.inc');

	// JSON related functions
	$config['load_core_class'][] = array('classname' => 'Json', 'folder' => 'formats');

	// Image thumbnail generation
	$config['load_add_core_class'][] = array('classname' => 'Thumbnail', 'autoload' => false);

	*/
	
	
/**
 * LOADING CUSTOM CLASSES
 *
 * You may create custom classes that exist outside of the system folder.
 */
	/*
	
	// Creating a custom class that we keep in the Index module
	$config['custom_classes'][] = array('classname' => 'Myclass', 'root'=>MODULES_PATH.DIRECTORY_SEPARATOR.'Index');

	// Extending an existing system class, but keeping the file in a folder called custom
	$config['custom_classes'][] = array('classname' => 'Myclass', 'root'=>APPLICATION_PATH.DIRECTORY_SEPARATOR.'custom');

	*/
	
?>