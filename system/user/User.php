<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Managers user accounts
 * @package Aspen_Framework
 */
class User extends Library {


	/**
	 * @var array Holds the permissions table results
	 */
	private $permissions = array();


	/**
	 * Loads the permissions table
	 */
	public function  aspen_init() {
		$this->loadPermissions();
	}


	/**
	 * Loads the permissions table results so we don't need to query the db constantly
	 * @access private
	 */
	public function loadPermissions(){
		$perms = $this->APP->model->open('permissions');
		$this->permissions = $perms->results();
	}


	/**
	 * Displays and processes the add/edit user form
	 * @access public
	 * @param integer $id
	 */
	public function edit($id = false){

		$result = false;
		$form = new Form('users', $id, array('groups'));
		$form->addField('password_confirm');

		// process the form if submitted
		if($form->isSubmitted()){

			// We need to validate the confirm password field here
			// because the model doesn't care about this field.
			$values = $form->getCurrentValues();
			if($values->isSetAndNotEmpty('password') || $values->isSetAndNotEmpty('password_confirm')){
				if($values->isSetAndEmpty('password') || $values->isSetAndEmpty('password_confirm')){
					$form->addError('password', 'You must enter and confirm your password.');
				} else {
					if(!$values->match('password', 'password_confirm')){
						$form->addError('password', 'Your passwords do not match.');
					}
				}
			}

			// save the data as well as the groups
			$result = $form->save($id);

		}

		$this->APP->template->set(array('form'=>$form));

		return $result;

	}


	/**
	 * Allows a user to change their own password
	 * @access public
	 */
	public function my_account(){
		return $this->edit($this->APP->params->session->getInt('user_id'));
	}


	/**
	 * Deletes a user record
	 * @param integer $id
	 * @access public
	 */
	public function delete($id = false){
		$auth = $this->APP->model->open('users');
		return $auth->delete($id);
	}


	/**
	 * Displays the login page
	 * @param string $module_path
	 * @access public
	 */
	final public function login(){

		$uri = $this->APP->params->server->getUri('REQUEST_URI').$this->APP->params->server->getRaw('QUERY_STRING');
		$uri = strip_tags(urldecode($uri));
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
	 * Displays the login failed message and returns to login
	 * @access public
	 */
	public function login_failed(){
		$this->APP->sml->say('Your username and password did not match. Please try again.', false);
		$this->APP->router->redirect('login',false,'Users');
	}


	/**
	 * Displays and processes the forgotten password system
	 * @access public
	 */
	public function forgot(){

		$form = new Form();
		$form->addFields(array('user'));

		// process the form if submitted
		if($form->isSubmitted()){

			// generate a new password
			$new_pass = $this->makePassword();

			// load the account
			$auth = $this->APP->model->open('users');
			$user = $auth->quickSelectSingle($form->cv('user'), 'username');

			if(is_array($user)){

				// update the account
				$user['password'] = $new_pass;
				$auth->update($user, $user['id']);

				if($this->APP->db->Affected_Rows()){

					// SEND THE EMAIL TO THE USER
					$this->APP->mail->AddAddress($form->cv('user'));
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
			} else {
				return -1;
			}
		}

		$this->APP->template->set(array('form'=>$form));

		return false;

	}


	/**
	 * Returns a machine-user-application specific string that's encoded and stored
	 * in the session and is used to verify that session.
	 * @return string
	 * @access private
	 */
	final private function getDomainKeyValue(){
		$string = $this->APP->config('application_guid') . LS;
		$string .= $this->APP->params->server->getServerName('HTTP_HOST');
		$string .= $this->APP->params->server->getServerName('HTTP_USER_AGENT');
		$string .= $this->APP->params->server->getServerName('REMOTE_ADDR');
		return sha1($string);
	}


	/**
	 * Returns a securely hashed string.
	 * @param <type> $pass
	 * @return <type>
	 */
	final private function stringHash($pass){
		$p = new PasswordHash();
		return $p->HashPassword($pass);
	}


	/**
	 * Handles authenticating the user
	 * @access public
	 */
	final public function authenticate(){

		$auth = false;
		$p	  = new PasswordHash();
		$user = $this->APP->params->post->getEmail('user');
		$pass = $this->APP->params->post->getRaw('pass');

		if($user && $pass){

			$model = $this->APP->model->open('users');
			$model->where('username', $user);
			$model->where('allow_login', 1);
			$model->limit(0, 1);
			$result = $model->results();

			if($result){
				foreach($result as $account){
					if($p->CheckPassword($pass, $account['password'])){

						$_SESSION['authenticated']		= true;
						$_SESSION['authentication_key'] = $this->getAuthenticationKey($account['username'], $account['id']);
						$_SESSION['domain_key'] 		= $this->getDomainKeyValue();
						$_SESSION['username'] 			= $account['username'];
						$_SESSION['nice_name'] 			= $account['nice_name'];
						$_SESSION['latest_login'] 		= $account['latest_login'];
						$_SESSION['last_login'] 		= $account['last_login'];
						$_SESSION['user_id'] 			= $account['id'];

						// run any post-auth logic
						$this->post_authentication($account);

						// update last login date
						$upd = array('last_login' => $account['latest_login'], 'latest_login' => date("Y-m-d H:i:s"));
						$model->update($upd, $account['id']);

						$auth = true;

					}
				}
			}
		}

		return $auth;

	}


	/**
	 * Allows users to run additional code during login without having to
	 * extend the authentication function itself.
	 */
	protected function post_authentication($account = false){
		return true;
	}


	/**
	 * Generates a unique authentication key.
	 * @param string $username
	 * @param string $user_id
	 * @return string
	 */
	public function getAuthenticationKey($username, $user_id){
		return sha1($username . $user_id);
	}


	/**
	 * Returns the post-login redirect URL if it's been set.
	 * @access public
	 */
	public function postLoginRedirect(){

		$redirect = false;
		if($this->APP->params->session->isPath('post-login_redirect')){
			$redirect = $this->APP->router->getDomainUrl();
			$redirect .= $this->APP->params->session->getPath('post-login_redirect');
			$lred = strtolower($redirect);
			if(strpos($lred, 'users/login') !== false || strpos($lred, 'users/authenticate') !== false){
				$redirect = false;
			}
		}

		return empty($redirect) ? $this->APP->router->getInterfaceUrl() : $redirect;

	}


	/**
	 * Returns whether or not the user is logged in
	 * @return boolean
	 * @access public
	 */
	final public function isLoggedIn(){

		$authenticated 	= false;
		$auth_key 		= sha1($this->APP->params->session->getEmail('username') . $this->APP->params->session->getInt('user_id'));

		if($this->APP->checkDbConnection()){
			if(
				$this->APP->params->session->getInt('authenticated', false) &&
				$this->APP->params->session->getAlnum('authentication_key') == $auth_key &&
				$this->APP->params->session->getAlnum('domain_key') == $this->getDomainKeyValue()
				){
					$authenticated = true;
			}
		}

		return $authenticated;

	}


	/**
	 * Checks if user has permissions to access an entire interface
	 * @param string $interface
	 * @param integer $user_id
	 * @return boolean
	 * @access public
	 */
	public function userHasInterfaceAccess($interface = false, $user_id = false){

		$authenticated 	= false;
		$interface 		= $interface ? $interface : LOADING_SECTION;
		$user_id		= $user_id ? $user_id : $this->APP->params->session->getInt('user_id');

		if(IS_ADMIN){
			$authenticated = true;
		} else {

			if($this->isLoggedIn()){

				// first identify any groups this user belongs to
				$model = $this->APP->model->open('user_group_link');
				$model->select(array('group_id'));
				$model->where('user_id', $user_id);
				$groups = $model->results();
				$groups = Utils::extract('/group_id', $groups);

				foreach($this->permissions as $perm){
					if(
						($perm['interface'] == $interface || $perm['interface'] == '*') &&
						(in_array($perm['group_id'], $groups) || $perm['user_id'] = $user_id)
					){
						$authenticated = true;
					}
				}
			}
		}

		return $authenticated;

	}


	/**
	 * Checks if user has permissions to access a page
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

		if(IS_ADMIN || $this->allowAnonymous($module, $method, $interface)){
			$authenticated = true;
		} else {

			if($this->isLoggedIn() && $method != 'logout'){

				// first identify any groups this user belongs to
				$model = $this->APP->model->open('user_group_link');
				$model->select(array('group_id'));
				$model->where('user_id', $user_id);
				$groups = $model->results();
				$groups = Utils::extract('/group_id', $groups);

				foreach($this->permissions as $perm){
					if(
						($perm['interface'] == $interface || $perm['interface'] == '*') &&
						($perm['module'] == $module || $perm['module'] == '*') &&
						($perm['method'] == $method || $perm['method'] == '*') &&
						(in_array($perm['group_id'], $groups) || $perm['user_id'] = $user_id)
					){
						$authenticated = true;
					}
				}
			}
		}

		return $authenticated;

	}


	/**
	 * Checks whether or not anonymous access is allowed for the current page.
	 * @param string $module
	 * @param string $method
	 * @param string $interface
	 * @return boolean
	 * @access public
	 */
	public function allowAnonymous($module = false, $method = false, $interface = false){

		if($module == 'Install' &&  $interface == 'Admin'){
			return true;
		}

		if($this->APP->isInstalled()){
			$module = ucwords(str_replace('_'.$interface, '', strtolower($module)));
			$interface = ucwords(strtolower($interface));

			foreach($this->permissions as $perm){
				if(
					($perm['interface'] == $interface || $perm['interface'] == '*') &&
					($perm['module'] == $module || $perm['module'] == '*') &&
					($perm['method'] == $method || $perm['method'] == '*') &&
					$perm['group_id'] === null && $perm['user_id'] == null
				){
					return true;
				}
			}
		}
		return false;

	}


	/**
	 * Logs a user out
	 * @access public
	 */
	public function logout(){
		$_SESSION = array();
		session_destroy();
	}


	/**
	 * Returns whether a user is in a group
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

			$ingroup = (boolean)$groups;

		}

		return $ingroup;

	}


	/**
	 * Returns an array of groups the user is in
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

			if($groups){
				foreach($groups as $id => $group){
					$ingroups[$id] = $group['name'];
				}
			}
		}

		return $ingroups;

	}


	/**
	 * Returns whether or not user has global access (admin/superuser)
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

			$has_access = $groups ? true : false;

		}

		return $has_access;

	}


	/**
	 * Counts the number of user accounts
	 * @return integer
	 * @access public
	 */
	public function userAccountCount(){

		if($this->APP->checkDbConnection()){

			$model = $this->APP->model->open('users');
			$accounts = $model->results();
			return count($accounts);

		} else {

			return 1;

		}
	}


	/**
	 * Returns the default module for a specific user group
	 * @access public
	 */
	 public function getUserDefaultModule(){

		$default = $this->APP->config('default_module');

		if($this->APP->isInstalled()){
			if($user_id = $this->APP->params->session->getInt('user_id')){
				$groups = array_keys( $this->usersGroups($user_id) );

				$ug_defs = $this->APP->config('usergroup_default_modules');

				foreach($groups as $group){
					if(array_key_exists($group, $ug_defs)){
						return $ug_defs[$group];
					}
				}
			}
		}

		return $default;
	}


	/**
	 * Generates new random password
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
	 * Returns a list of groups
	 * @return array
	 * @access public
	 */
	public function groupList(){

		$model = $this->APP->model->open('groups');
		$model->orderBy('name');
		$groups = $model->results();
		return $groups;

	}
}
?>