<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Managers user accounts
 * @package Aspen_Framework
 */
class User {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	protected $APP;


	/**
	 * @abstract Constructor, initializes the module
	 * @return User
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance(); }


	/**
	 * @abstract Displays and processes the add new user form
	 * @access public
	 * @param integer $id
	 */
	public function add(){

		$id = false;

		$this->APP->form->load('authentication');
		$this->APP->form->addField('password_confirm');
		$this->APP->form->addField('group', array(), array());

		// process the form if submitted
		if($this->APP->form->isSubmitted()){

			// validation
			if(!$this->APP->form->isFilled('username')){
				$this->APP->form->addError('username', 'You must enter a username.');
			} else {

				// verify unique
				$this->APP->model->select('authentication');
				$this->APP->model->where('username', $this->APP->form->cv('username'));
				$unique = $this->APP->model->results();

				if($unique['RECORDS']){
					$this->APP->form->addError('username', 'That username has already been used.');
				}
			}

			// We need to validate the confirm password field here
			// because the model doesn't care about this field.
			if($this->APP->form->isFilled('password')){
				if(!$this->APP->form->isFilled('password_confirm')){
					$this->APP->form->addError('password', 'You must confirm your password.');
				} else {
					if(!$this->APP->form->fieldsMatch('password', 'password_confirm')){
						$this->APP->form->addError('password', 'Your passwords do not match.');
					}
				}
			}

			// validate the user has selected a group
			$groups = $this->APP->form->cv('group');
			if(empty($groups)){
				$this->APP->form->addError('group', 'You must select at least one user group.');
			}

			// save the data as well as the groups
			if($id = $this->APP->form->save()){
				$group_model = $this->APP->model->open('user_group_link');
				foreach($this->APP->form->cv('group') as $group){
					$group_model->insert(array('user_id' => $id, 'group_id' => $group));
				}
			}
		}

		return $id;

	}


	/**
	 * @abstract Displays and processes the edit user form
	 * @access public
	 * @param integer $id
	 */
	public function edit($id){

		$this->APP->form->load('authentication', $id);
		$this->APP->form->addField('password_confirm');

		// pull all groups this user is associated with
		$group_array = array();

		$model = $this->APP->model->open('user_group_link');
		$model->where('user_id', $id);
		$groups = $model->results();
		if($groups['RECORDS']){
			foreach($groups['RECORDS'] as $group){
				$group_array[] = $group['group_id'];
			}
		}
		$this->APP->form->addField('group', $group_array, $group_array);


		// process the form if submitted
		if($this->APP->form->isSubmitted()){

			// validation
			if(!$this->APP->form->isFilled('username')){
				$this->APP->form->addError('username', 'You must enter a username.');
			}

			if($this->APP->form->isFilled('password_confirm')){
				if(!$this->APP->form->fieldsMatch('password', 'password_confirm')){
					$this->APP->form->addError('password', 'Your passwords do not match.');
				}
			}

			$groups = $this->APP->form->cv('group');
			if(empty($groups)){
				$this->APP->form->addError('group', 'You must select at least one user group.');
			}

			// if allow_login not present, set to false
			$this->APP->form->setCurrentValue('allow_login', $this->APP->params->post->getInt('allow_login', false));

			if(!$this->APP->form->error()){

				$upd = array(
						'username' => $this->APP->form->cv('username'),
						'nice_name' => $this->APP->form->cv('nice_name'),
						'allow_login' => $this->APP->form->cv('allow_login')
				);

				if($this->APP->form->isFilled('password')){
					$upd['password'] = sha1($this->APP->form->cv('password'));
				}

				if($this->APP->model->executeUpdate('authentication', $upd, $id)){

					$groups = $this->APP->form->cv('group');

					// if user is admin, we can't permit them to remove the admin group
					// from themselves
					if(IS_ADMIN && $id == $this->APP->params->session->getInt('user_id')){
						if(!in_array(1, $groups)){
							$groups[] = 1;
						}
					}

					// remove existing groups
					$this->APP->model->delete('user_group_link', $id, 'user_id');

					// add new user groups
					foreach($groups as $group){
						$this->APP->model->executeInsert('user_group_link', array('user_id' => $id, 'group_id' => $group));
					}

					return true;

				}
			}
		}

		return false;

	}


	/**
	 * @abstract Allows a user to change their own password
	 * @access public
	 */
	public function my_account(){

		// add these two values since it's all we need right now
		$this->APP->form->addFields(array('password_1', 'password_2'));

		// if form submitted
		if($this->APP->form->isSubmitted()){

			// load values
			$this->APP->form->loadPOST();

			// validate that passwords set and match
			if($this->APP->form->isFilled('password_1')){
				if($this->APP->form->cv('password_1') != $this->APP->form->cv('password_2')){
					$this->APP->form->addError('password_1', 'Your passwords must match.');
				}
			} else {
				$this->APP->form->addError('password_1', 'Your password may not be blank.');
			}

			// if we have no errors, update password in user authentication table
			if(!$this->APP->form->error()){

				return $this->APP->model->executeUpdate(
										'authentication',
										array('password' => sha1($this->APP->form->cv('password_1'))),
										$this->APP->params->session->getInt('user_id'));


			}
		}

		return false;

	}


	/**
	 * @abstract Deletes a user record
	 * @param integer $id
	 * @access public
	 */
	public function delete($id = false){
		if($id){
			$this->APP->model->delete('authentication', $id);
			$this->APP->sml->addNewMessage('User account has been deleted successfully.');
			return true;
		}
		return false;
	}


	/**
	 * @abstract Displays the login page
	 * @param string $module_path
	 * @access public
	 */
	public function login(){

		$uri = $this->APP->params->server->getRaw('REQUEST_URI');
		$uri .= $this->APP->params->server->getRaw('QUERY_STRING');
		$uri = preg_replace('/redirected=(.*)/', '', $uri);

		// set the forwarding url if any set pre-login
		if(
			$this->APP->config('post_login_redirect') && !strpos($uri, 'install') &&
			!strpos($uri, 'users&method=login') && !strpos($uri, 'users/login') &&
			!strpos($uri, 'users&method=forgot') && !strpos($uri, 'users/forgot') &&
			!strpos($uri, 'users&method=authenticate') && !strpos($uri, 'users/authenticate')
		){
			$_SESSION['post-login_redirect'] = $uri;
		} else {
			$_SESSION['post-login_redirect'] = $this->APP->router->getInterfaceUrl();
		}
	}


	/**
	 * @abstract Displays the login failed message and returns to login
	 * @access public
	 */
	public function login_failed(){
		$this->APP->form->addError('user', 'Your username and password did not match. Please try again.');
		$this->APP->Users_Admin->login();
	}


	/**
	 * @abstract Displays and processes the forgotten password system
	 * @access public
	 */
	public function forgot(){

		$this->APP->form->addFields(array('user'));

		// process the form if submitted
		if($this->APP->form->isSubmitted()){

			// validation
			if(!$this->APP->form->isFilled('user')){
				$this->APP->form->addError('user', 'Please enter your username.');
			}

			if(!$this->APP->form->error()){

				// generate a new password
				$new_pass = $this->makePassword();

				// update the account
				$this->APP->model->executeUpdate('authentication', array('password' => sha1($new_pass)), strtolower($this->APP->form->cv('user')), 'LOWER(username)');

				if($this->APP->db->Affected_Rows()){

					// SEND THE EMAIL TO THE USER
					$this->APP->mail->AddAddress($this->APP->form->cv('user'));
					$this->APP->mail->From      	= $this->APP->config('email_sender');
					$this->APP->mail->FromName  	= $this->APP->config('email_sender_name');
					$this->APP->mail->Mailer    	= "mail";
					$this->APP->mail->ContentType 	= 'text/html';
					$this->APP->mail->Subject   	= $this->APP->config('password_reset_subject');
					$this->APP->mail->Body 			= str_replace('{new_pass}', $new_pass, $this->APP->config('password_reset_body'));

					$this->APP->mail->Send();
					$this->APP->mail->ClearAddresses();

					return 1;

				}
			}

			return -1;

		}

		return false;

	}


	/**
	 * @abstract Handles authenticating the user
	 * @access public
	 */
	public function authenticate(){

		$auth = false;
		$user = $this->APP->params->post->getRaw('user');
		$pass = sha1($this->APP->params->post->getRaw('pass'));

		if($user && $pass){

			$model = $this->APP->model->open('authentication');
			$model->where('password', $pass);
			$model->where('username', $user);
			$model->where('allow_login', 1);
			$model->limit(0, 1);
			$result = $model->results();

			if($result['RECORDS']){
				foreach($result['RECORDS'] as $account){

					$auth = true;

					$_SESSION['authenticated']		= true;
					$_SESSION['authentication_key'] = sha1($account['username'] . $account['id']);
					$_SESSION['domain_key'] 		= sha1($this->APP->params->server->getRaw('HTTP_HOST'));
					$_SESSION['username'] 			= $account['username'];
					$_SESSION['nice_name'] 			= $account['nice_name'];
					$_SESSION['latest_login'] 		= $account['latest_login'];
					$_SESSION['last_login'] 		= $account['last_login'];
					$_SESSION['user_id'] 			= $account['id'];

					// update last login date
					$upd = array('last_login' => $account['latest_login'], 'latest_login' => date("Y-m-d H:i:s"));
					$model = $this->APP->model->open('authentication', $upd, $account['id']);
					$model->update($upd, $account['id']);

					$auth = true;

				}
			}
		}

		return $auth;

	}


	/**
	 * @abstract Returns whether or not the user is logged in
	 * @return boolean
	 * @access public
	 */
	public function isLoggedIn(){

		$authenticated 	= false;
		$auth_key 		= sha1($this->APP->params->session->getRaw('username') . $this->APP->params->session->getInt('user_id'));
		$domain_key 	= sha1($this->APP->params->server->getRaw('HTTP_HOST'));

		if($this->APP->checkDbConnection()){
			if(
				$this->APP->params->session->getInt('authenticated', false) &&
				$this->APP->params->session->getAlnum('authentication_key') == $auth_key &&
				$this->APP->params->session->getAlnum('domain_key') == $domain_key
				){
					$authenticated = true;
			}
		}

		return $authenticated;

	}


	/**
	 * @abstract Checks if user has permissions to access an entire interface
	 * @param string $interface
	 * @param integer $user_id
	 * @return boolean
	 * @access public
	 */
	public function userHasInterfaceAccess($interface = false, $user_id = false){

		if(!$this->APP->requireLogin()){
			return true;
		}

		$authenticated 	= false;
		$interface 		= $interface ? $interface : LOADING_SECTION;
		$user_id		= $user_id ? $user_id : $this->APP->params->session->getInt('user_id');

		if($this->userHasGlobalAccess()){

			$authenticated = true;

		} else {

			if($this->isLoggedIn()){

				// first identify any groups this user belongs to
				$model = $this->APP->model->open('user_group_link');
				$model->select(array('group_id'));
				$model->where('user_id', $user_id);
				$groups = $model->results();

				$group_where = '';

				if($groups['RECORDS']){
					foreach($groups['RECORDS'] as $group){
						$group_where .= '
							OR group_id = ' . $group['group_id'];
					}
				}


				// auth if:
				// interface matches current or is all
				// module matches current or is all
				// method matches current or is all
				$strict_sql = sprintf('
							SELECT * FROM permissions
							WHERE (interface = "%s" OR interface = "*") AND (user_id = %s%s)',
								$interface, $user_id, $group_where);

				$stricts = $this->APP->model->query($strict_sql);
				$authenticated = $stricts->RecordCount() ? true : false;

			}
		}

		return $authenticated;

	}


	/**
	 * @abstract Checks if user has permissions to access a page
	 * @param string $module
	 * @param string $method
	 * @param string $interface
	 * @param integer $user_id
	 * @return boolean
	 * @access public
	 */
	public function userHasAccess($module = false, $method = false, $interface = false, $user_id = false){

		$authenticated 	= false;
		$module 		= $module ? $module : $this->APP->router->getSelectedModule();
		$method 		= $method ? $method : $this->APP->router->getSelectedMethod();
		$interface 		= $interface ? $interface : LOADING_SECTION;
		$user_id		= $user_id ? $user_id : $this->APP->params->session->getInt('user_id');
		$module 		= str_replace('_'.$interface, '', $module);

		if(
			$this->userHasGlobalAccess() ||
			!$this->APP->requireLogin() ||
			($module == 'Users' &&
					($method == 'login' || $method == 'authenticate' || $method == 'logout' || $method == 'forgot') ||
				$module == 'Install')){

			return true;

		} else {

			if($this->isLoggedIn() && $method != 'logout'){

				// first identify any groups this user belongs to
				$model = $this->APP->model->open('user_group_link');
				$model->select(array('group_id'));
				$model->where('user_id', $user_id);
				$groups = $model->results();

				$group_where = '';

				if($groups['RECORDS']){
					foreach($groups['RECORDS'] as $group){
						$group_where .= '
							OR group_id = ' . $group['group_id'];
					}
				}


				// auth if:
				// interface matches current or is all
				// module matches current or is all
				// method matches current or is all
				$strict_sql = sprintf('
							SELECT * FROM permissions
							WHERE (interface = "%s" OR interface = "*") AND (module = "%s" OR module = "*") AND (method="%s" OR method = "*") AND (user_id = %s%s)',
								$interface, $module, $method, $user_id, $group_where);

				$stricts = $this->APP->model->query($strict_sql);
				$authenticated = $stricts->RecordCount() ? true : false;

			}
		}

		return $authenticated;

	}


	/**
	 * @abstract Logs a user out
	 * @access public
	 */
	public function logout(){
		$_SESSION = array();
		session_destroy();
	}


	/**
	 * @abstract Returns whether a user is in a group
	 * @param string $group_name
	 * @param integer $user_id
	 * @return boolean
	 */
	public function inGroup($group_name, $user_id = false){

		$ingroup = false;

		if($user_id){

			$model = $this->APP->model->open('user_group_link');
			$model->leftJoin('groups', 'id', 'group_id', array('name'));
			$model->where('user_id', $user_id);
			$model->where('groups.name', $group_name);
			$groups = $model->results();

			$ingroup = (boolean)$groups['RECORDS'];

		}

		return $ingroup;

	}


	/**
	 * @abstract Returns an array of groups the user is in
	 * @param integer $user_id
	 * @return boolean
	 * @access public
	 */
	public function usersGroups($user_id = false){

		$ingroups = array();

		if($user_id){

			$model = $this->APP->model->open('user_group_link');
			$model->leftJoin('groups', 'id', 'group_id', array('name'));
			$model->where('user_id', $user_id);
			$groups = $model->results();

			if($groups['RECORDS']){
				foreach($groups['RECORDS'] as $group){
					$ingroups[] = $group['name'];
				}
			}
		}

		return $ingroups;

	}


	/**
	 * @abstract Returns whether or not user has global access (admin/superuser)
	 * @return boolean
	 * @access public
	 */
	public function userHasGlobalAccess(){

		$has_access = false;

		if($this->isLoggedIn()){

			$model = $this->APP->model->open('user_group_link');
			$model->where('user_id', $this->APP->params->session->getInt('user_id'));
			$model->where('group_id', 1);
			$groups = $model->results();

			$has_access = $groups['RECORDS'] ? true : false;

		}

		return $has_access;

	}


	/**
	 * @abstract Counts the number of user accounts
	 * @return integer
	 * @access public
	 */
	public function userAccountCount(){

		if($this->APP->checkDbConnection()){

			$model = $this->APP->model->open('authentication');
			$accounts = $model->results();
			return count($accounts['RECORDS']);

		} else {

			return 1;

		}
	}


	/**
	 * @abstract Generates new random password
	 * @param integer $length
	 * @return string
	 * @access public
	 */
	public function makePassword($length = 5){

		$password = "";
		$possible = "0123456789abcdfghjkmnpqrstvwxyz~!@#$%^&_-+";
		$i = 0;

		// add random characters to $password until $length is reached
		while ($i < $length) {

			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

			// we don't want this character if it's already in the password
			if (!strstr($password, $char)) {
				$password .= $char;
				$i++;
			}
    	}

		return $password;

	}


	/**
	 * @abstract Returns a list of groups
	 * @return array
	 * @access public
	 */
	public function groupList(){

		$model = $this->APP->model->open('groups');
		$model->orderBy('name');
		$groups = $model->results();
		return $groups['RECORDS'];

	}
}
?>