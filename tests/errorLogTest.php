<?php

require_once('testHelper.php');

/**
 * error test case.
 */
class errorLogTest extends TestHelper {
	
	/**
	 * @abstract
	 */
	public function testNoErrors() {
		
		$this->sharedFixture->model->select('error_log');
		$errors = $this->sharedFixture->model->results();
		
		$this->assertEquals(false, $errors['RECORDS']);
	}
}
