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
	 * Displays and processes the add/edit user form
	 * @access public
	 * @param integer $id
	 */
	public function edit($id = false){

		$form = new Form('authentication', $id);
		$form->addField('password_confirm');

		// pull all groups this user is associated with
		$group_array = array();
		if($id){
			$model = $this->APP->model->open('user_group_link');
			$model->where('user_id', $id);
			$groups = $model->results();
			if($groups['RECORDS']){
				foreach($groups['RECORDS'] as $group){
					$group_array[] = $group['group_id'];
				}
			}
		}
		$form->addField('group', $group_array, $group_array);


		// process the form if submitted
		if($form->isSubmitted()){

			// We need to validate the confirm password field here
			// because the model doesn't care about this field.
			$values = Peregrine::sanitize( $form->getCurrentValues() );
			if($values->isSetAndEmpty('password')){
				if($values->isSetAndNotEmpty('password_confirm')){
					$form->addError('password', 'You must confirm your password.');
				} else {
					if(!$values->match('password', 'password_confirm')){
						$form->addError('password', 'Your passwords do not match.');
					}
				}
			}

			// validate the groups
			$groups = $form->cv('group');
			if(empty($groups)){
				$form->addError('group', 'You must select at least one user group.');
			}

			// if allow_login not present, set to false
			$form->setCurrentValue('allow_login', $this->APP->params->post->getInt('allow_login', false));

			// save the data as well as the groups
			if($result = $form->save($id)){

				/**
				 * Add in new groups
				 */
				$groups = $form->cv('group');

				// if user is admin, we can't permit them to remove the admin group
				// from themselves
				if(IS_ADMIN && $id == $this->APP->params->session->getInt('user_id')){
					if(!in_array(1, $groups)){
						$groups[] = 1;
					}
				}

				// remove existing and add in new groups
				$group_model = $this->APP->model->open('user_group_link');
				$group_model->delete($id, 'user_id');
				foreach($groups as $group){
					$group_model->insert(array('user_id' => (int)$id, 'group_id' => (int)$group));
				}

				return $result;

			}
		}

		$this->APP->template->set(array('form'=>$form));

		return false;

	}


	/**
	 * Allows a user to change their own password
	 * @access public
	 */
	public function my_account(){

		$id = $this->APP->params->session->getInt('user_id');

		$form = new Form('authentication', $id);
		$form->addField('password_confirm');

		// if form submitted
		if($form->isSubmitted('post','user-submit')){

			// We need to validate the confirm password field here
			// because the model doesn't care about this field.
			if($form->isFilled('password')){
				if(!$form->isFilled('password_confirm')){
					$form->addError('password', 'You must confirm your password.');
				} else {
					if(!$form->fieldsMatch('password', 'password_confirm')){
						$form->addError('password', 'Your passwords do not match.');
					}
				}
			}

			return $form->save($id);
			
		}

		return false;

	}


	/**
	 * Deletes a user record
	 * @param integer $id
	 * @access public
	 */
	public function delete($id = false){
		if($id){
			$auth = $this->APP->model->open('authentication');
			return $auth->delete($id);
		}
		return false;
	}


	/**
	 * Displays the login page
	 * @param string $module_path
	 * @access public
	 */
	public function login(){

		$uri = $this->APP->params->server->getRaw('REQUEST_URI').$this->APP->params->server->getRaw('QUERY_STRING');
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
			$auth = $this->APP->model->open('authentication');
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

		return false;

	}


	/**
	 * Returns a string used for specific domain/application id
	 * @return string
	 * @access private
	 */
	private function getDomainKeyValue(){
		$string = $this->APP->config('application_guid') . LS;
		$string .= $this->APP->params->server->getRaw('HTTP_HOST');
		return $string;
	}


	/**
	 * Handles authenticating the user
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
					$_SESSION['authentication_key'] = $this->getAuthenticationKey($account['username'], $account['id']);
					$_SESSION['domain_key'] 		= sha1($this->getDomainKeyValue());
					$_SESSION['username'] 			= $account['username'];
					$_SESSION['nice_name'] 			= $account['nice_name'];
					$_SESSION['latest_login'] 		= $account['latest_login'];
					$_SESSION['last_login'] 		= $account['last_login'];
					$_SESSION['user_id'] 			= $account['id'];

					// update last login date
					$upd = array('last_login' => $account['latest_login'], 'latest_login' => date("Y-m-d H:i:s"));
					$model = $this->APP->model->open('authentication');
					$model->update($upd, $account['id']);

					$auth = true;

				}
			}
		}

		return $auth;

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
	public function isLoggedIn(){

		$authenticated 	= false;
		$auth_key 		= sha1($this->APP->params->session->getRaw('username') . $this->APP->params->session->getInt('user_id'));
		$domain_key 	= sha1($this->getDomainKeyValue());

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

		if($this->userHasGlobalAccess() || $this->allowAnonymous($module, $method, $interface)){
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

			$sql = sprintf('
					SELECT * FROM permissions
					WHERE (interface = "%s" OR interface = "*") AND (module = "%s" OR module = "*") AND (method="%s" OR method = "*") AND user_id IS NULL AND group_id IS NULL',
						$interface, $module, $method);

			$access = $this->APP->model->query($sql);
			return $access->RecordCount() ? true : false;
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

			$ingroup = (boolean)$groups['RECORDS'];

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

			if($groups['RECORDS']){
				foreach($groups['RECORDS'] as $id => $group){
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

			$has_access = $groups['RECORDS'] ? true : false;

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

			$model = $this->APP->model->open('authentication');
			$accounts = $model->results();
			return count($accounts['RECORDS']);

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
		return $groups['RECORDS'];

	}
}
?>