<?php

require('../system/loader.inc.php');

require_once 'AppTest.php';
require_once 'BootstrapTest.php';
require_once 'CacheTest.php';
require_once 'ErrorTest.php';
require_once 'FileTest.php';
require_once 'FormTest.php';
require_once 'InstallTest.php';
require_once 'JsonTest.php';
require_once 'LogTest.php';
require_once 'ModelTest.php';
require_once 'ModulesTest.php';
require_once 'PreferencesTest.php';
require_once 'ScaffoldTest.php';
require_once 'RouterTest.php';
require_once 'SecurityTest.php';
require_once 'SettingsTest.php';
require_once 'SmlTest.php';
require_once 'UserTest.php';
require_once 'XmlTest.php';

require_once 'Index_AdminTest.php';
require_once 'errorLogTest.php';

/**
 * Static test suite.
 */
class testsSuite extends PHPUnit_Framework_TestSuite {
	
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		// no code here because we need to catch the system pre-headers for session_start() in aspen
	}
	
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		$this->sharedFixture = null;
	}
	
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		
		// load the framework
		$this->sharedFixture = load_framework('Admin');
		
		// wipe out any tables needed for the tests
		$this->sharedFixture->model->query("TRUNCATE `config`");
		$this->sharedFixture->model->query("TRUNCATE `error_log`");
		
		$this->setName ( 'testsSuite' );
		
		$this->addTestSuite ( 'AppTest' );
		$this->addTestSuite ( 'BootstrapTest' );
		$this->addTestSuite ( 'CacheTest' );
		$this->addTestSuite ( 'ErrorTest' );
		$this->addTestSuite ( 'FileTest' );
		$this->addTestSuite ( 'FormTest' );
		$this->addTestSuite ( 'InstallTest' );
		//$this->addTestSuite ( 'JsonTest' );
		$this->addTestSuite ( 'LogTest' );
		$this->addTestSuite ( 'ModelTest' );
		$this->addTestSuite ( 'ModulesTest' );
		$this->addTestSuite ( 'PreferencesTest' );
		$this->addTestSuite ( 'RouterTest' );
		$this->addTestSuite ( 'ScaffoldTest' );
		$this->addTestSuite ( 'SecurityTest' );
		$this->addTestSuite ( 'SettingsTest' );
		$this->addTestSuite ( 'SmlTest' );
		$this->addTestSuite ( 'UserTest' );
		//$this->addTestSuite ( 'XmlTest' );

		$this->addTestSuite( 'Index_AdminTest' );
		$this->addTestSuite( 'errorLogTest' );

	}
	
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ( );
	}
}

