<?php

require_once 'testHelper.php';

/**
 * Preferences test case.
 */
class PreferencesTest extends TestHelper {
	
	
	/**
	 * Tests Preferences->addSort()
	 */
	public function testAddSort() {
		$this->assertEquals('', $this->sharedFixture->prefs->addSort('users:list', 'username', 'DESC'));
	}
	
	
	/**
	 * Tests Preferences->getSort()
	 */
	public function testGetSort() {
		$sort = $this->sharedFixture->prefs->getSort('users:list');
		$this->assertEquals(array('sort_by'=>'id','sort_direction'=>'ASC','is_default'=>true), $sort);
	}
}

