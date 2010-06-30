#!/usr/bin/php -q
<?php

define('LOADING_SECTION', '');
define('INCLUDE_ONLY', true);

// define our application paths
define('APPLICATION_PATH', str_replace(DIRECTORY_SEPARATOR . "modules", '', dirname(__FILE__)));
define('SYSTEM_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'system');
define('MODULES_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules');
define('PLUGINS_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'plugins');

// Include the bootstrap file
require(SYSTEM_PATH.DIRECTORY_SEPARATOR.'bootstrap.php');

// load the configs
$config = Bootstrap::loadAllConfigs();
$APP = new Bootstrap($config);
$_SERVER = $APP->params->getRawSource('server');

// BEGIN PROCESSING COMMAND LINE INPUT
	if(array_key_exists('HTTP_USER_AGENT', $_SERVER)){
		exit(0);
	}

	/**
	 * Verify and process arguments passed to script
	 */
	if (array_key_exists('argv', $_SERVER)){

		// allowed: add, remove
		$allowed_actions = array('add', 'remove');

		// verify the first argument
		if(array_key_exists(1,$_SERVER['argv'])) {

			$action = $_SERVER['argv'][1];

			if(!in_array($action, $allowed_actions)){
				die("Invalid action. Use ".implode(", ", $allowed_actions).".\n");
			}

		} else {
			die("Missing required action to perform.\n");
		}


		// verify second argument, which is module name
		if(array_key_exists(2,$_SERVER['argv'])) {

			$module = ucwords($_SERVER['argv'][2]);

			// validate module name is acceptable
			if(empty($module) || !ctype_alnum($module)){
				die("Invalid module name: ".$module.".\n");
			}

		} else {
			die("Missing required name of new module.\n");
		}


		// verify third argument, which is interface name (not required)
		$interface = '';
		if(array_key_exists(3,$_SERVER['argv'])) {

			$interface = $_SERVER['argv'][3];

			// validate module name is acceptable
			if(empty($interface) || !ctype_alnum($interface)){
				die("Invalid interface name: ".$interface.".\n");
			} else {
				$interface = '_' . ucwords($interface);
			}

		}

	} else {
		die("Missing arguments, please check documentation on use.\n");
	}

	// append the interface name
	$classname = $module . $interface;


	// verify the config file loaded - if not, error out
	$config = array();
	require(str_replace("modules", "", dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.php');

	// open a database connection
	$conn = mysql_connect($config['db_hostname'], $config['db_username'], $config['db_password']);
	mysql_select_db($config['db_database']);

	// pusee if we have a matching table for this module
	$table_exists = false;
	$table_name = false;
	$show_tables = mysql_query('SHOW TABLES');
	while($table = mysql_fetch_array($show_tables)){
		if(strtolower($table[0]) == strtolower($module)){
			$table_exists = true;
			$table_name = strtolower($module);
		}
	}


	/**
	 * ADD A NEW MODULE
	 */
	if($action == 'add'){

		// debug ONLY
		shell_exec('rm -rf ' . $module);

		/**
		 * Generates a new GUID for a module
		 * @return string
		 * @access private
		 */
		function NewGuid() {
		    $s = strtoupper(md5(uniqid(rand(),true)));
		    $guidText =
		        substr($s,0,8) . '-' .
		        substr($s,8,4) . '-' .
		        substr($s,12,4). '-' .
		        substr($s,16,4). '-' .
		        substr($s,20);
		    return strtolower($guidText);
		}
		$guid  = NewGuid();



		// make our directories
		shell_exec('mkdir ' . dirname(__FILE__) . DIRECTORY_SEPARATOR . $module);
		shell_exec('mkdir ' . dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'templates' . strtolower($interface));
		shell_exec('mkdir ' . dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'language');


	// CREATE THE MODULE CLASS
$class_output = "<?php

/**
 * ".$classname." class
 * @author Aspen Framework modadmin
 */
class ".$classname." extends Module {


	/**
	 * Displays the default template for this module.
	 * @access public
	 */
	public function view(){";

	if($table_exists){

	$class_output .= "

		\$data = array();

		\$model = \app()->model->open('".$table_name."');
		\$data['".$table_name."'] = \$model->results();";
	}

	$class_output .= "

		\app()->template->addView(\app()->template->getTemplateDir().DS . 'header.tpl.php');
		\app()->template->addView(\app()->template->getModuleTemplateDir().DS . 'index.tpl.php');
		\app()->template->addView(\app()->template->getTemplateDir().DS . 'footer.tpl.php');
		\app()->template->display(".($table_name ? "\$data" : "").");

	}";


if($table_exists){

$class_output .= "


	/**
	 * Displays and processes the add ".$table_name." form
	 * @access public
	 */
	public function add(){
		\$this->edit();
	}


	/**
	 * Displays and processes the edit ".$table_name." form
	 * @param integer \$id
	 * @access public
	 */
	public function edit(\$id = false){

		\$form = new Form('".$table_name."', \$id);

		// if form has been submitted
		if(\$form->isSubmitted()){

			// insert a new record with available data
			if(\$form->save(\$id)){
				// if successful insert, redirect to the list
				\app()->router->redirect('view');
			}
		}

		\app()->template->addView(\app()->template->getTemplateDir().DS . 'header.tpl.php');
		\app()->template->addView(\app()->template->getModuleTemplateDir().DS . 'edit.tpl.php');
		\app()->template->addView(\app()->template->getTemplateDir().DS . 'footer.tpl.php');
		\app()->template->display(array('form'=>\$form));

	}


	/**
	 * Deletes a single ".$table_name." record
	 * @param integer \$id
	 * @access public
	 */
	public function delete(\$id = false){
		if(\app()->model->delete('".$table_name."', \$id)){
			\app()->router->redirect('view');
		}
	}";

}

$class_output .= "
}
?>";

		touch(dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $classname . '.php');
		$file = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $classname . '.php', 'w');
		fwrite($file, $class_output);
		fclose($file);


	// CREATE THE INDEX TEMPLATE
	if($table_exists){
		$template_output = $APP->scaffold->view_php($table_name);
	} else {
		$template_output = "<h2>New Module</h2>\n";
		$template_output .= "<p>Your new module has been created and installed. You're ready to begin development! To get you started, here are some helpful links:</p>\n";
		$template_output .= "<ul>\n";
		$template_output .= "<li><a href=\"http://docs.aspen-framework.org/wiki/Aspen:Creating_A_Module\">Creating A Module</a></li>\n";
		$template_output .= "<li><a href=\"http://docs.aspen-framework.org\">Main Documentation</a></li>\n";
		$template_output .= "</ul>\n";
	}

	$file_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'templates' . strtolower($interface) . DIRECTORY_SEPARATOR . 'index.tpl.php';
	touch($file_path);
	$file = fopen($file_path, 'w');
	fwrite($file, $template_output);
	fclose($file);


	// CREATE THE EDIT FORM TEMPLATE
	if($table_exists){

		$template_output = $APP->scaffold->recordForm($table_name, 'Edit', false, true);

		$file_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'templates' . strtolower($interface) . DIRECTORY_SEPARATOR . 'edit.tpl.php';
		touch($file_path);
		$file = fopen($file_path, 'w');
		fwrite($file, $template_output);
		fclose($file);

	}


	// CREATE THE LANGUAGE FILE
	if($table_exists){

		$template_output = $APP->scaffold->languageFile();

		$file_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'en_US.php';
		touch($file_path);
		$file = fopen($file_path, 'w');
		fwrite($file, $template_output);
		fclose($file);

	}


	// CREATE THE REGISTER.XML FILE

  		$xml = '<?xml version="1.0"?>';
  		$xml .= '<package>' . "\n";
  		$xml .= '	<type>Module</type>' . "\n";
  		$xml .= sprintf('	<guid>%s</guid>', $guid) . "\n";
  		$xml .= sprintf('	<classname>%s</classname>', $module) . "\n";
  		$xml .= sprintf('	<name>%s</name>', $module) . "\n";
  		$xml .= '</package>';

		touch(dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'register.xml');
		$file = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'register.xml', 'w');
		fwrite($file, $xml);
		fclose($file);


		// save new module to database
		$query = sprintf('INSERT INTO modules (guid) VALUES ("%s")', $guid);
		mysql_query($query);

		// if query fails, print out the guid with db save instructions

		fwrite(STDOUT, 'A new module called "'.$module.'" has been created successfully.' . "\n");

	}



	/**
	 * REMOVE A MODULE
	 */
	if($action == 'remove'){


		// grab xml guid from register.xml file
		$raw_xml = simplexml_load_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . $module . '/register.xml');
		$guid = isset($raw_xml->guid) ? (string)$raw_xml->guid : false;

		// remove guid from db
		mysql_query(sprintf('DELETE FROM modules WHERE guid = "%s"', $guid));

		// remove module folder
		shell_exec('rm -rf ' . $module);

		fwrite(STDOUT, '"'.$module.'" has been removed successfully.' . "\n");

	}

?>