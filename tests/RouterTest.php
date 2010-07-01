<?php

require_once 'testHelper.php';

/**
 * Router test case.
 */
class RouterTest extends TestHelper {
	

	/**
	 * Tests Router->decodeForRewriteUrl()
	 */
	public function testDecodeForRewriteUrl(){
		$this->assertEquals('A String', $this->sharedFixture->router->decodeForRewriteUrl('A_String'));
	}

	/**
	 * Tests Router->encodeForRewriteUrl()
	 */
	public function testEncodeForRewriteUrl(){
		$this->assertEquals('A_String', $this->sharedFixture->router->encodeForRewriteUrl('A String'));
	}
	
	
	/**
	 * Tests Router->arg()
	 */
	public function testArg()
	{
		// TODO Auto-generated RouterTest->testArg()
		$this->markTestIncomplete("arg test not implemented");

		$this->sharedFixture->router->arg(/* parameters */);

	}

	/**
	 * Tests Router->appUrl()
	 */
	public function testappUrl()
	{
		// TODO Auto-generated RouterTest->testappUrl()
		$this->markTestIncomplete("appUrl test not implemented");

		$this->sharedFixture->router->appUrl(/* parameters */);

	}

	/**
	 * Tests Router->domainUrl()
	 */
	public function testdomainUrl()
	{
		// TODO Auto-generated RouterTest->testdomainUrl()
		$this->markTestIncomplete("domainUrl test not implemented");

		$this->sharedFixture->router->domainUrl(/* parameters */);

	}

	/**
	 * Tests Router->fullUrl()
	 */
	public function testfullUrl()
	{
		// TODO Auto-generated RouterTest->testfullUrl()
		$this->markTestIncomplete("fullUrl test not implemented");

		$this->sharedFixture->router->fullUrl(/* parameters */);

	}

	/**
	 * Tests Router->interfaceUrl()
	 */
	public function testinterfaceUrl()
	{
		// TODO Auto-generated RouterTest->testinterfaceUrl()
		$this->markTestIncomplete("interfaceUrl test not implemented");

		$this->sharedFixture->router->interfaceUrl(/* parameters */);

	}

	/**
	 * Tests Router->getMappedArguments()
	 */
	public function testGetMappedArguments()
	{
		// TODO Auto-generated RouterTest->testGetMappedArguments()
		$this->markTestIncomplete("getMappedArguments test not implemented");

		$this->sharedFixture->router->getMappedArguments(/* parameters */);

	}

	/**
	 * Tests Router->getModulePath()
	 */
	public function testGetModulePath()
	{
		// TODO Auto-generated RouterTest->testGetModulePath()
		$this->markTestIncomplete("getModulePath test not implemented");

		$this->sharedFixture->router->getModulePath(/* parameters */);

	}

	/**
	 * Tests Router->moduleUrl()
	 */
	public function testmoduleUrl()
	{
		// TODO Auto-generated RouterTest->testmoduleUrl()
		$this->markTestIncomplete("moduleUrl test not implemented");

		$this->sharedFixture->router->moduleUrl(/* parameters */);

	}

	/**
	 * Tests Router->getParentModule()
	 */
	public function testGetParentModule()
	{
		// TODO Auto-generated RouterTest->testGetParentModule()
		$this->markTestIncomplete("getParentModule test not implemented");

		$this->sharedFixture->router->getParentModule(/* parameters */);

	}

	/**
	 * Tests Router->getPath()
	 */
	public function testGetPath()
	{
		// TODO Auto-generated RouterTest->testGetPath()
		$this->markTestIncomplete("getPath test not implemented");

		$this->sharedFixture->router->getPath(/* parameters */);

	}

	/**
	 * Tests Router->port()
	 */
	public function testport()
	{
		// TODO Auto-generated RouterTest->testport()
		$this->markTestIncomplete("port test not implemented");

		$this->sharedFixture->router->port(/* parameters */);

	}

	/**
	 * Tests Router->getSelectedArguments()
	 */
	public function testGetSelectedArguments()
	{
		// TODO Auto-generated RouterTest->testGetSelectedArguments()
		$this->markTestIncomplete("getSelectedArguments test not implemented");

		$this->sharedFixture->router->getSelectedArguments(/* parameters */);

	}

	/**
	 * Tests Router->method()
	 */
	public function testmethod()
	{
		// TODO Auto-generated RouterTest->testmethod()
		$this->markTestIncomplete("method test not implemented");

		$this->sharedFixture->router->method(/* parameters */);

	}

	/**
	 * Tests Router->module()
	 */
	public function testmodule()
	{
		// TODO Auto-generated RouterTest->testmodule()
		$this->markTestIncomplete("model test not implemented");

		$this->sharedFixture->router->module(/* parameters */);

	}

	/**
	 * Tests Router->staticUrl()
	 */
	public function teststaticUrl()
	{
		// TODO Auto-generated RouterTest->teststaticUrl()
		$this->markTestIncomplete("staticUrl test not implemented");

		$this->sharedFixture->router->staticUrl(/* parameters */);

	}

	/**
	 * Tests Router->uploadsUrl()
	 */
	public function testuploadsUrl()
	{
		// TODO Auto-generated RouterTest->testuploadsUrl()
		$this->markTestIncomplete("uploadsUrl test not implemented");

		$this->sharedFixture->router->uploadsUrl(/* parameters */);

	}

	/**
	 * Tests Router->here()
	 */
	public function testHere()
	{
		// TODO Auto-generated RouterTest->testHere()
		$this->markTestIncomplete("here test not implemented");

		$this->sharedFixture->router->here(/* parameters */);

	}

	/**
	 * Tests Router->identifyModuleForLoad()
	 */
	public function testIdentifyModuleForLoad()
	{
		// TODO Auto-generated RouterTest->testIdentifyModuleForLoad()
		$this->markTestIncomplete("identifyModuleForLoad test not implemented");

		$this->sharedFixture->router->identifyModuleForLoad(/* parameters */);

	}

	/**
	 * Tests Router->loadFromUrl()
	 */
	public function testLoadFromUrl()
	{
		// TODO Auto-generated RouterTest->testLoadFromUrl()
		$this->markTestIncomplete("loadFromUrl test not implemented");

		$this->sharedFixture->router->loadFromUrl(/* parameters */);

	}


	/**
	 * Tests Router->setReturnToReferrer()
	 */
	public function testSetReturnToReferrer()
	{
		// TODO Auto-generated RouterTest->testSetReturnToReferrer()
		$this->markTestIncomplete("setReturnToReferrer test not implemented");

		$this->sharedFixture->router->setReturnToReferrer(/* parameters */);

	}
}