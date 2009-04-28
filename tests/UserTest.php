<?php

require_once 'testHelper.php';

/**
 * User test case.
 */
class UserTest extends TestHelper {

	
	/**
	 * Tests User->add()
	 */
	public function testAddFailsUsername() {
		$user = $this->sharedFixture->model->open('authentication');
		$this->assertEquals(false, $user->insert(array('username'=>'')) );
	}
	
	/**
	 * Tests User->add()
	 */
	public function testAddFailsPassword() {
		$user = $this->sharedFixture->model->open('authentication');
		$this->assertEquals(false, $user->insert(array('username'=>'unit-tester')) );
	}

	
	/**
	 * Tests User->add()
	 */
	public function testAddSendsValidRecord() {
		$user = $this->sharedFixture->model->open('authentication');
		$this->assertEquals(2, $user->insert(array('username'=>'unit-tester','password'=>'test','pass_confirm'=>'test')) );
	}
	
	/**
	 * Tests User->authenticate()
	 */
	public function testAuthenticate() {
		// TODO Auto-generated UserTest->testAuthenticate()
		$this->markTestIncomplete ( "authenticate test not implemented" );
		
		//->authenticate(/* parameters */);
	
	}
	
	/**
	 * Tests User->delete()
	 */
	public function testDelete() {
		// TODO Auto-generated UserTest->testDelete()
		$this->markTestIncomplete ( "delete test not implemented" );
		
		//->delete(/* parameters */);
	
	}
	
	/**
	 * Tests User->edit()
	 */
	public function testEdit() {
		// TODO Auto-generated UserTest->testEdit()
		$this->markTestIncomplete ( "edit test not implemented" );
		
		//->edit(/* parameters */);
	
	}
	
	/**
	 * Tests User->forgot()
	 */
	public function testForgot() {
		// TODO Auto-generated UserTest->testForgot()
		$this->markTestIncomplete ( "forgot test not implemented" );
		
		//->forgot(/* parameters */);
	
	}
	
	/**
	 * Tests User->groupList()
	 */
	public function testGroupList() {
		$groups = $this->sharedFixture->user->groupList();
		$this->assertEquals(true, is_array($groups));
		$this->assertEquals('Administrator', $groups[1]['name']);
	}
	
	/**
	 * Tests User->inGroup()
	 */
	public function testInGroup() {
		$this->assertEquals(true, $this->sharedFixture->user->inGroup('Administrator', 1));
	}
	
	/**
	 * Tests User->isLoggedIn()
	 */
	public function testIsLoggedIn() {
		$this->assertEquals(false, $this->sharedFixture->user->isLoggedIn());
	}
	
	/**
	 * Tests User->login()
	 */
	public function testLogin() {
		// TODO Auto-generated UserTest->testLogin()
		$this->markTestIncomplete ( "login test not implemented" );
		
		//->login(/* parameters */);
	
	}
	
	/**
	 * Tests User->login_failed()
	 */
	public function testLogin_failed() {
		// TODO Auto-generated UserTest->testLogin_failed()
		$this->markTestIncomplete ( "login_failed test not implemented" );
		
		//->login_failed(/* parameters */);
	
	}

	
	/**
	 * Tests User->makePassword()
	 */
	public function testMakePassword() {
		$pass = $this->sharedFixture->user->makePassword(8);
		$this->assertEquals(true, is_string($pass));
		$this->assertEquals(8, strlen($pass));
	}
	
	/**
	 * Tests User->my_account()
	 */
	public function testMy_account() {
		// TODO Auto-generated UserTest->testMy_account()
		$this->markTestIncomplete ( "my_account test not implemented" );
		
		//->my_account(/* parameters */);
	
	}
	
	/**
	 * Tests User->userAccountCount()
	 */
	public function testUserAccountCount() {
		// TODO Auto-generated UserTest->testUserAccountCount()
		$this->markTestIncomplete ( "userAccountCount test not implemented" );
		
		$this->assertEquals(1, $this->sharedFixture->user->userAccountCount());
	
	}
	
	/**
	 * Tests User->userHasAccess()
	 */
	public function testUserHasAccess() {
		$this->assertEquals(true, $this->sharedFixture->user->userHasAccess('Settings'));
		$this->markTestIncomplete ( "due to bug 1238, this passes when it should be false for admin app" );
	
	}
	
	/**
	 * Tests User->userHasGlobalAccess()
	 */
	public function testUserHasGlobalAccess() {
		$this->assertEquals(false, $this->sharedFixture->user->userHasGlobalAccess());
	}
	
	/**
	 * Tests User->usersGroups()
	 */
	public function testUsersGroups() {
		$grps = $this->sharedFixture->user->usersGroups(1);
		$this->assertEquals('Administrator', $grps[0]);
	}

}

