<?php

require_once('testHelper.php');

/**
 * error test case.
 */
class errorLogTest extends TestHelper {
	
	/**
	 *
	 */
	public function testNoErrors() {
		
		$model = $this->sharedFixture->model->open('error_log');
		$errors = $model->results();
		
		$this->assertEquals(false, $errors);
	}
}
