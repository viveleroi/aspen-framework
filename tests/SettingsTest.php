<?php

require_once 'testHelper.php';

/**
 * Settings test case.
 */
class SettingsTest extends TestHelper {

	/**
	 * Tests Settings->getConfig()
	 */
	public function testGetConfig_noExist() {
		$this->assertEquals(false, $this->sharedFixture->settings->getConfig('exampleconfig'));
	}
	
	/**
	 * Tests Settings->setConfig()
	 */
	public function testSetConfig_NoKey() {
		$this->assertEquals(false, $this->sharedFixture->settings->setConfig(''));
	}
	
	/**
	 * Tests Settings->setConfig()
	 */
	public function testSetConfig() {
		$this->sharedFixture->settings->setConfig('exampleconfig', 'testvalue');
		$this->assertEquals('testvalue', $this->sharedFixture->settings->getConfig('exampleconfig'));
	}

}

