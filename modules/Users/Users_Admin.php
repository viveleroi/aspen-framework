<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	Modules.Base
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Handles forms for user accounts
 * @package Aspen_Framework
 * @uses Module
 */
class Users_Admin extends Module {


	/**
	 * Displays the list of users
	 * @access public
	 */
	public function view(){

		$model = app()->model->open('users');
		$model->contains('groups');
		$model->orderBy('username', 'ASC');
		$data['users'] = $model->results();

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'index.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display($data);

	}


	/**
	 * Displays and processes the add a new user form
	 * @access public
	 */
	public function add(){
		$this->edit();
	}


	/**
	 * Displays and processes the edit user form
	 * @access public
	 * @param $id The id of the user record
	 */
	public function edit($id = false){

		if(app()->user->edit($id)){
			app()->sml->addNewMessage('User account changes have been saved successfully.', true);
			app()->router->redirect('view');
		}

		$data['groups'] = app()->user->groupList();

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'edit.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display($data);

	}


	/**
	 * Displays and processes the my account form
	 * @access public
	 */
	public function my_account(){

		if(app()->user->my_account()){
			app()->sml->addNewMessage('Your account has been updated successfully.', true);
			app()->router->redirect('view', false, 'Index');
		}

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'my_account.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display($data);

	}


	/**
	 * Deletes a user record
	 * @param integer $id The record id of the user
	 * @access public
	 */
	public function delete($id = false){
		if(app()->user->delete($id)){
			app()->sml->addNewMessage('User account has been deleted successfully.', true);
			app()->router->redirect('view');
		}
	}


	/**
	 * Displays the user login page
	 * @access public
	 */
	public function login(){

		app()->user->login();

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'login.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display();
	}


	/**
	 * Displays and processes the forgotten password reset form
	 * @access public
	 */
	public function forgot(){

		if(app()->user->forgot() == 1){
			app()->sml->addNewMessage('Your password has been reset. Please check your email.', true);
			app()->router->redirect('login');
		}
		elseif(app()->user->forgot() == -1){
			app()->sml->addNewMessage('We were unable to find any accounts matching that username.', false);
			app()->router->redirect('forgot');
		}

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'forgot.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display();

	}


	/**
	 * Runs the authentication process on the login form data
	 * @access public
	 */
	public function authenticate(){
		if(app()->user->authenticate()){
			app()->router->redirectToUrl(app()->user->postLoginRedirect());
			exit;
		} else {
			app()->user->login_failed();
		}
	}


	/**
	 * Processes a logout
	 * @access public
	 */
	public function logout(){
		app()->user->logout();
		app()->router->redirectToUrl(app()->router->getInterfaceUrl());
	}


	/**
	 * Displays a permission denied error message
	 * @access public
	 */
	public function denied(){
		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(app()->template->getModuleTemplateDir().DS . 'denied.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display();
	}
}
?>