<?php

require_once 'testHelper.php';

/**
 * Sml test case.
 */
class SmlTest extends TestHelper {
	

	/**
	 * Tests Sml->addNewMessage()
	 */
	public function testAddNewMessage() {
		$this->assertEquals(true, $this->sharedFixture->sml->addNewMessage('This is a phpunit sml message.'));
		$this->sharedFixture->params->refreshCage('session');
	}
	
	/**
	 * Tests Sml->getMessageLog()
	 */
	public function testGetMessageLog() {
		$log = $this->sharedFixture->sml->getMessageLog();
		$this->assertEquals(array('This is a phpunit sml message.'), $log);
	}
	
	/**
	 * Tests Sml->getMostRecentMessage()
	 */
	public function testGetMostRecentMessage() {
		$log = $this->sharedFixture->sml->getMostRecentMessage();
		$this->assertEquals('This is a phpunit sml message.', $log);
	}
	
}

