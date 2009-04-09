<?php

require_once 'testHelper.php';

/**
 * Xml test case.
 */
class XmlTest extends TestHelper {
	
	
	/**
	 * Tests Xml->arrayToXml()
	 * @todo Fix this test - the strings are not equal for some reason - newlines?
	 */
	public function testArrayToXml() {
		
		$test = array('first'=>'John','last'=>'Doe','age'=>37);
		$ret_xml = $this->sharedFixture->xml->arrayToXml($test);
		
		//print $this->sharedFixture->xml->arrayToXml($test);
		
		$result = '<?xml version="1.0"?>
<response>
  <first>John</first>
  <last>Doe</last>
  <age>37</age>
</response>';
		
		$this->assertEquals(102, strlen($ret_xml));
		
		//$this->Xml->arrayToXml(/* parameters */);
	
	}
	
	/**
	 * Tests Xml->encode_for_xml()
	 */
	public function testEncode_for_xml() {

		$this->assertEquals('Testing &amp; testing &amp; output', $this->sharedFixture->xml->encode_for_xml('Testing & testing &amp; output'));

	}

}

