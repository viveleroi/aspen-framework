<?php

require_once 'testHelper.php';

/**
 * Json test case.
 */
class JsonTest extends TestHelper {
	
	
	/**
	 * Tests Json->php_json_encode()
	 */
	public function testPhp_json_encode() {
		$test = array('first'=>'John','last'=>'Doe','age'=>37);
		$ret_json = $this->sharedFixture->json->php_json_encode($test);
		
		$this->assertEquals('{"first":"John","last":"Doe","age":37}', $ret_json);
	
	}

}

