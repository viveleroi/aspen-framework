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
	 * Tests Router->getApplicationUrl()
	 */
	public function testGetApplicationUrl()
	{
		// TODO Auto-generated RouterTest->testGetApplicationUrl()
		$this->markTestIncomplete("getApplicationUrl test not implemented");

		$this->sharedFixture->router->getApplicationUrl(/* parameters */);

	}

	/**
	 * Tests Router->getDomainUrl()
	 */
	public function testGetDomainUrl()
	{
		// TODO Auto-generated RouterTest->testGetDomainUrl()
		$this->markTestIncomplete("getDomainUrl test not implemented");

		$this->sharedFixture->router->getDomainUrl(/* parameters */);

	}

	/**
	 * Tests Router->getFullUrl()
	 */
	public function testGetFullUrl()
	{
		// TODO Auto-generated RouterTest->testGetFullUrl()
		$this->markTestIncomplete("getFullUrl test not implemented");

		$this->sharedFixture->router->getFullUrl(/* parameters */);

	}

	/**
	 * Tests Router->getInterfaceUrl()
	 */
	public function testGetInterfaceUrl()
	{
		// TODO Auto-generated RouterTest->testGetInterfaceUrl()
		$this->markTestIncomplete("getInterfaceUrl test not implemented");

		$this->sharedFixture->router->getInterfaceUrl(/* parameters */);

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
	 * Tests Router->getModuleUrl()
	 */
	public function testGetModuleUrl()
	{
		// TODO Auto-generated RouterTest->testGetModuleUrl()
		$this->markTestIncomplete("getModuleUrl test not implemented");

		$this->sharedFixture->router->getModuleUrl(/* parameters */);

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
	 * Tests Router->getPort()
	 */
	public function testGetPort()
	{
		// TODO Auto-generated RouterTest->testGetPort()
		$this->markTestIncomplete("getPort test not implemented");

		$this->sharedFixture->router->getPort(/* parameters */);

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
	 * Tests Router->getSelectedMethod()
	 */
	public function testGetSelectedMethod()
	{
		// TODO Auto-generated RouterTest->testGetSelectedMethod()
		$this->markTestIncomplete("getSelectedMethod test not implemented");

		$this->sharedFixture->router->getSelectedMethod(/* parameters */);

	}

	/**
	 * Tests Router->getSelectedModule()
	 */
	public function testGetSelectedModule()
	{
		// TODO Auto-generated RouterTest->testGetSelectedModule()
		$this->markTestIncomplete("getSelectedModule test not implemented");

		$this->sharedFixture->router->getSelectedModule(/* parameters */);

	}

	/**
	 * Tests Router->getStaticContentUrl()
	 */
	public function testGetStaticContentUrl()
	{
		// TODO Auto-generated RouterTest->testGetStaticContentUrl()
		$this->markTestIncomplete("getStaticContentUrl test not implemented");

		$this->sharedFixture->router->getStaticContentUrl(/* parameters */);

	}

	/**
	 * Tests Router->getUploadsUrl()
	 */
	public function testGetUploadsUrl()
	{
		// TODO Auto-generated RouterTest->testGetUploadsUrl()
		$this->markTestIncomplete("getUploadsUrl test not implemented");

		$this->sharedFixture->router->getUploadsUrl(/* parameters */);

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