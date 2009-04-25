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
		
		$model = $this->sharedFixture->model->openAndSelect('error_log');
		$errors = $model->results();
		
		$this->assertEquals(false, $errors['RECORDS']);
	}
}
