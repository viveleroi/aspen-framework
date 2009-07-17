<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	Modules.Base
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Handles forms for user accounts
 * @package Aspen_Framework
 * @uses Module
 */
class Users_Admin extends Module {	

	
	/**
	 * @abstract Displays the list of users
	 * @access public
	 */
	public function view(){

		$model = $this->APP->model->open('authentication');
		$model->orderBy('username', 'ASC');
		$data['users'] = $model->results();

		$model = $this->APP->model->open('groups');
		$model->orderBy('name', 'ASC');
		$data['groups'] = $model->results();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'index.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);

	}


	/**
	 * @abstract Displays and processes the add a new user form
	 * @access public
	 */
	public function add_group(){
		$this->edit_group();
	}


	/**
	 * @abstract Displays and processes the add a new user form
	 * @access public
	 */
	public function edit_group($id = false){

		if($this->APP->user->edit_group($id)){
			$this->APP->sml->addNewMessage('User group has been updated successfully.');
			$this->APP->router->redirect('view');
		}

		$data['values'] = $this->APP->form->getCurrentValues();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'edit_group.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);

	}


	/**
	 * @abstract Displays and processes the add a new user form
	 * @access public
	 */
	public function add(){
		$this->edit();
	}
	

	/**
	 * @abstract Displays and processes the edit user form
	 * @access public
	 * @param $id The id of the user record
	 */
	public function edit($id = false){

		if($this->APP->user->edit($id)){
			$this->APP->sml->addNewMessage('User account changes have been saved successfully.');
			$this->APP->router->redirect('view');
		}
		
		$data['groups'] = $this->APP->user->groupList();
		$data['values'] = $this->APP->form->getCurrentValues();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'edit.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);

	}
	
	
	/**
	 * @abstract Displays and processes the my account form
	 * @access public
	 */
	public function my_account(){

		if($this->APP->user->my_account()){
			$this->APP->sml->addNewMessage('Your account has been updated successfully.');
			$this->APP->router->redirect('view', false, 'Index');
		}

		$data['values'] = $this->APP->form->getCurrentValues();

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'my_account.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display($data);

	}


	/**
	 * @abstract Deletes a user record
	 * @param integer $id The record id of the user
	 * @access public
	 */
	public function delete($id = false){
		if($this->APP->user->delete($id)){
			$this->APP->sml->addNewMessage('User account has been deleted successfully.');
			$this->APP->router->redirect('view');
		}
	}
	
	
	/**
	 * @abstract Runs the authentication process on the login form data
	 * @access public
	 */
	public function authenticate(){
		if($this->APP->user->authenticate()){
			$redirect = $this->APP->params->session->getRaw('post-login_redirect');
			$redirect = empty($redirect) ? $this->APP->router->getInterfaceUrl() : $redirect;
			header("Location: " . $redirect);
			exit;
		} else {
			$this->APP->user->login_failed();
		}
	}

	
	/**
	 * @abstract Processes a logout
	 * @access public
	 */
	public function logout(){
		$this->APP->user->logout();
		$this->APP->router->redirect('view', false, 'Index');
	}


	/**
	 * @abstract Displays a permission denied error message
	 * @access public
	 */
	public function denied(){
		$this->APP->template->addView($this->APP->template->getTemplateDir() . '/header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'denied.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir() . '/footer.tpl.php');
		$this->APP->template->display();
	}
	

	/**
	 * @abstract Displays the user login page
	 * @access public
	 */
	public function login(){
		
		$this->APP->user->login();
		
		$this->APP->template->addView($this->APP->template->getTemplateDir() . '/header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'login.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir() . '/footer.tpl.php');
		$this->APP->template->display();
	}

	
	/**
	 * @abstract Displays and processes the forgotten password reset form
	 * @access public
	 */
	public function forgot(){

		if($this->APP->user->forgot() == 1){
			$this->APP->sml->addNewMessage('Your password has been reset. Please check your email.');
			$this->APP->router->redirect('login');
		}
		elseif($this->APP->user->forgot() == -1){
			$this->APP->sml->addNewMessage('We were unable to find any accounts matching that username.');
			$this->APP->router->redirect('forgot');
		}

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView($this->APP->template->getModuleTemplateDir().DS . 'forgot.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display();

	}
}
?>