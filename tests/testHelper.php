<?php

/**
 * Pageslib test case.
 */
class TestHelper extends PHPUnit_Framework_TestCase {

	
	/**
	 * @abstract Mocks a form post for test purposes
	 * @param array $post
	 */
	public function mockFormPost($post) {
		$_POST = $post;
		$_POST['submit'] = 'submit';
		$this->sharedFixture->params->refreshCage('post');
	}
	
	
	/**
	 * @abstract Mocks a get request for test purposes
	 * @param array $post
	 */
	public function mockGet($get) {
		$_GET = $get;
		$this->sharedFixture->params->refreshCage('get');
	}
}