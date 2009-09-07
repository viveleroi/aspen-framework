<?php

require_once 'testHelper.php';

/**
 * Bootstrap test case.
 */
class BootstrapTest extends TestHelper {
	
	//$this->assertEquals(true, $this->sharedFixture->awaitingUpgrade());
	
	
	/**
	 * Tests Bootstrap->awaitingUpgrade()
	 */
	public function testAwaitingUpgrade() {
		$this->assertEquals(false, $this->sharedFixture->awaitingUpgrade());
	}
	
	/**
	 * Tests Bootstrap->callPluginHook()
	 */
	public function testCallPluginHook() {
		// TODO Auto-generated BootstrapTest->testCallPluginHook()
		$this->markTestIncomplete ( "callPluginHook test not implemented" );
	
	}
	
	/**
	 * Tests Bootstrap->checkDbConnection()
	 */
	public function testCheckDbConnection() {
		$this->assertEquals(true, $this->sharedFixture->checkDbConnection());
	
	}
	
	/**
	 * Tests Bootstrap::checkUserConfigExists()
	 */
	public function testCheckUserConfigExists() {
		$this->assertEquals(true, Bootstrap::checkUserConfigExists());
	
	}
	
	/**
	 * Tests Bootstrap->config()
	 */
	public function testInvalidConfig() {
		$this->assertEquals(false, $this->sharedFixture->config('noconfignameexists'));
	
	}
	
	/**
	 * Tests Bootstrap->config()
	 */
	public function testConfig() {
		$cfg = $this->sharedFixture->config('application_name');
		$this->assertEquals(false, empty($cfg));
	
	}
	
	/**
	 * Tests Bootstrap->getConfig()
	 */
	public function testGetConfig() {
		$cfg = $this->sharedFixture->getConfig();
		$this->assertEquals(true, is_array($cfg));
		$this->assertEquals(true, isset($cfg['application_name']));
	
	}
	
	/**
	 * Tests Bootstrap->getInstalledModuleGuids()
	 */
	public function testGetInstalledModuleGuids() {
		$cfg = $this->sharedFixture->getInstalledModuleGuids();
		$this->assertEquals(true, is_array($cfg));
	
	}
	
	/**
	 * Tests Bootstrap->getLoadedLibraries()
	 */
	public function testGetLoadedLibraries() {
		$cfg = $this->sharedFixture->getLoadedLibraries();
		$this->assertEquals(true, is_array($cfg));
	
	}
	
	/**
	 * Tests Bootstrap->getModuleRegistry()
	 */
	public function testGetModuleRegistry() {
		$cfg = $this->sharedFixture->getModuleRegistry();
		$this->assertEquals(true, is_array($cfg));
	
	}
	
	/**
	 * Tests Bootstrap->isInstalled()
	 */
	public function testIsInstalled() {
		$this->assertEquals(true, $this->sharedFixture->isInstalled());
	
	}
	
	/**
	 * Tests Bootstrap->isLibraryLoaded()
	 */
	public function testIsLibraryLoaded() {
		$this->assertEquals(true, $this->sharedFixture->isLibraryLoaded('HTMLPurifier'));
	
	}

	
	/**
	 * Tests Bootstrap->listModules()
	 */
	public function testListModules() {
		$this->assertEquals(true, $this->sharedFixture->listModules());
	}
	
	/**
	 * Tests Bootstrap::loadAllConfigs()
	 */
	public function testLoadAllConfigs() {
		// TODO Auto-generated BootstrapTest::testLoadAllConfigs()
		
		//$cfg = Bootstrap::loadAllConfigs();
		//$this->assertEquals(true, is_array($cfg));
		//$this->assertGreaterThan(1, count($cfg));
	
	}
	
	/**
	 * Tests Bootstrap::loadDefaultConfig()
	 */
	public function testLoadDefaultConfig() {
		// TODO Auto-generated BootstrapTest::testLoadDefaultConfig()
		$this->markTestIncomplete ( "loadDefaultConfig test not implemented" );
	
	}
	
	/**
	 * Tests Bootstrap->loadModule()
	 */
	public function testLoadModule() {
		$this->assertEquals(true, $this->sharedFixture->loadModule('f801e330-c7ba-11dc-95ff-0800200c9a66'));
	
	}
	
	/**
	 * Tests Bootstrap->loadSystemLibraryArray()
	 */
	public function testLoadSystemLibraryArray() {
		// TODO Auto-generated BootstrapTest->testLoadSystemLibraryArray()
		$this->markTestIncomplete ( "loadSystemLibraryArray test not implemented" );
	
	}
	
	/**
	 * Tests Bootstrap->moduleRegistry()
	 */
	public function testModuleRegistry() {
		
		$reg = $this->sharedFixture->moduleRegistry('f801e330-c7ba-11dc-95ff-0800200c9a66');
		$this->assertEquals(true, is_object($reg));
		
		$this->assertEquals('Settings', (string)$reg->classname);
	
	}
	
	/**
	 * Tests Bootstrap->parsePluginRegistries()
	 */
	public function testParsePluginRegistries() {
		// TODO Auto-generated BootstrapTest->testParsePluginRegistries()
		$this->markTestIncomplete ( "parsePluginRegistries test not implemented" );
	
	}
	
	/**
	 * Tests Bootstrap->setConfig()
	 */
	public function testSetConfig() {
		$this->sharedFixture->setConfig('setconfigsetvalue', 'phpunit');
		$this->assertEquals('phpunit', $this->sharedFixture->config('setconfigsetvalue'));
	
	}
	
	
	/**
	 * Tests Bootstrap->formatVersionNumber()
	 */
	public function testformatVersionNumber() {
		$this->assertEquals('1.1', $this->sharedFixture->formatVersionNumber('1.1'));
		$this->assertEquals('1.1', $this->sharedFixture->formatVersionNumber('1-1'));
		$this->assertEquals('1.1', $this->sharedFixture->formatVersionNumber('1.1-eghma'));
		$this->assertEquals('1', $this->sharedFixture->formatVersionNumber('abs1..1'));
	}
	
	
	/**
	 * Tests Bootstrap->versionCompare()
	 */
	public function testversionCompare() {
		$this->assertEquals('equal', $this->sharedFixture->versionCompare('1.1', '1.1'));
		$this->assertEquals('equal', $this->sharedFixture->versionCompare('1.1.0', '1.1'));
		$this->assertEquals('equal', $this->sharedFixture->versionCompare('1.1', '1.1.0'));
		$this->assertEquals('equal', $this->sharedFixture->versionCompare('1.1-egha', '1.1'));
		
		$this->assertEquals('less', $this->sharedFixture->versionCompare('1.0.1', '1.1'));
		$this->assertEquals('greater', $this->sharedFixture->versionCompare('10.1', '1.1'));

		$this->assertEquals('less', $this->sharedFixture->versionCompare('1.0.1', '1.0.1-256'));
		$this->assertEquals('equal', $this->sharedFixture->versionCompare('1.0.1.256.0', '1.0.1-256'));
	}
	
}

