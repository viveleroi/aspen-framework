<?php

/**
 * @package     Aspen_Framework
 * @subpackage  System
 * @author      Michael Botsko
 * @copyright   2009 Trellis Development, LLC
 * @since       1.0
 */


/**
 * Shortcut to return an instance of our original app
 * @return object
 */
function &user(){
    return app()->user;
}


/**
 * Managers user accounts
 * @package Aspen_Framework
 */
class User  {


    /**
     * Holds boolean for whether or not user has been authenticated
     * @var type
     */
    private $authenticated = false;


    /**
     * @var array Holds the permissions table results
     */
    protected $permissions = array();


    /**
     * Loads the permissions table results so we don't need to query the db constantly
     * @access private
     */
    public function loadPermissions(){
        // first identify any groups this user belongs to
        if(app()->session->keyExists('user_id')){
            $model = model()->open('user_group_link');
            $model->select(array('group_id'));
            $model->where('user_id', session()->getInt('user_id'));
            $groups = $model->results();
            $this->users_groups = Utils::extract('/group_id', $groups);
        }
        $perms = model()->open('permissions');
        $this->permissions = $perms->results();
    }


    /**
     * @abstract Displays and processes the signup form
     * @access public
     * @param integer $id
     */
    public function signup(){

        // if the user is logged in, send to interface
        if($this->isLoggedIn()){
            router()->redirectToUrl( app()->router->interfaceUrl() );
        }

        $result = false;
        $form = new Form('users', false, array('groups'));
        $form->addFields(array('password_confirm'));

        // process the form if submitted
        if($form->isSubmitted()){
            $form->setCurrentValue('allow_login', 1);
            $form->setCurrentValue('Groups', array(2));
            $result = $form->save();
        }

        // send the confirmation email
        if($result){
            app()->mail->AddAddress($form->cv('email'));
            app()->mail->SetFrom( config()->get('email_sender'), config()->get('email_sender_name'));
            app()->mail->AddReplyTo(config()->get('email_sender'));
            app()->mail->Mailer    	= "sendmail";
            app()->mail->ContentType= 'text/html';
            app()->mail->Subject    = text('signup:email:subject', config()->get('application_name'));

            $body = text('signup:email:body');
            $body = str_replace('{first_name}', $form->cv('first_name'), $body);
            $body = str_replace('{username}', $form->cv('username'), $body);
            $body = str_replace('{email}', $form->cv('email'), $body);
            $body = str_replace('{app}', config()->get('application_name'), $body);
            $body = str_replace('{url}', Url::path('users/login'), $body);
            $body = str_replace('{forgot}', Url::path('users/forgot'), $body);
            app()->mail->Body       = $body;

            app()->mail->Send();
            app()->mail->ClearAddresses();
        }

        template()->set(array('form'=>$form));

        return $result;

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
            $result = $form->save($id);
        }

        template()->set(array('form'        =>$form));
        template()->set(array('preferences' =>app()->prefs->edit( session()->getInt('user_id') )));
        template()->set(array('timezones'   =>Date::timezone_list('america')));

        return $result;

    }


    /**
     * Allows a user to change their own password
     * @access public
     */
    public function my_account(){
        return $this->edit(session()->getInt('user_id'));
    }


    /**
     * Deletes a user record
     * @param integer $id
     * @access public
     */
    public function delete($id = false){
        $auth = model()->open('users');
        return $auth->delete($id);
    }


    /**
     * Displays the login page
     * @param string $module_path
     * @access public
     */
    final public function login(){

        // if the user is logged in, send to interface
        if($this->isLoggedIn()){
            router()->redirectToUrl( router()->interfaceUrl() );
        }

        $uri = app()->server->getPath('REQUEST_URI').app()->server->getRaw('QUERY_STRING');
        $uri = strip_tags(urldecode($uri));
        $uri = preg_replace('/redirected=(.*)/', '', $uri);

        // set the forwarding url if any set pre-login
        if(
            config()->get('post_login_redirect') && !strpos($uri, 'install') &&
            !strpos($uri, 'users&method=login') && !strpos($uri, 'users/login') &&
            !strpos($uri, 'users&method=forgot') && !strpos($uri, 'users/forgot') &&
            !strpos($uri, 'users&method=authenticate') && !strpos($uri, 'users/authenticate')
        ){
            $_SESSION['post-login_redirect'] = router()->domainUrl().$uri;
        } else {
            $_SESSION['post-login_redirect'] = router()->interfaceUrl();
        }
    }


    /**
     * Displays the login failed message and returns to login
     * @access public
     */
    public function login_failed(){
        sml()->say(text('users:login:say:error'), false);
        router()->redirect('users/login');
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
            $auth = model()->open('users');
            $user = $auth->quickSelectSingle($form->cv('user'), 'email');

            if(is_array($user)){

                // update the account
                $user['password'] = $user['password_confirm'] = $new_pass;
                $auth->update($user, $user['id']);

                app()->mail->AddAddress($form->cv('user'));
                app()->mail->SetFrom( config()->get('email_sender'), config()->get('email_sender_name'));
                app()->mail->AddReplyTo(config()->get('email_sender'));
                app()->mail->Mailer    	= "sendmail";
                app()->mail->ContentType= 'text/html';
                app()->mail->Subject    = config()->get('password_reset_subject');
                app()->mail->Body       = str_replace('{new_pass}', $new_pass, config()->get('password_reset_body'));
                app()->mail->Send();
                app()->mail->ClearAddresses();
                return 1;
            } else {
                return -1;
            }
        }

        template()->set(array('form'=>$form));

        return false;

    }


    /**
     * Returns a machine-user-application specific string that's encoded and stored
     * in the session and is used to verify that session.
     * @return string
     * @access private
     */
    protected function getDomainKeyValue(){
        $string = config()->get('application_guid') . LS;
        $string .= app()->server->getServerName('HTTP_HOST');
        $string .= app()->server->getServerName('HTTP_USER_AGENT');
        $string .= app()->server->getServerName('REMOTE_ADDR');
        return sha1($string);
    }


    /**
     * Handles authenticating the user
     * @access public
     */
    public function authenticate( $user = false, $pass = false ){

        $data = array(
            'user' => $user,
            'pass' => $pass
        );
        $clean = Peregrine::sanitize($data);

        $auth = false;
        $p    = new PasswordHash();

        if(($clean->isEmail('user') || $clean->getUsername('user')) && !$clean->isEmpty('pass')){

            $model = model()->open('users');
            if($clean->isEmail('user')){
                $model->where('LOWER(email)', strtolower($clean->getEmail('user')));
            }
            elseif($clean->getUsername('user')){
                $model->where('LOWER(username)', strtolower($clean->getUsername('user')));
            }
            $model->where('allow_login', 1);
            $model->limit(0, 1);
            $result = $model->results();

            if($result){
                foreach($result as $account){
                    if($p->CheckPassword($clean->getRaw('pass'), $account['password'])){

                        $this->_registerAuthentication( $account );

                        // run any post-auth logic
                        $this->post_authentication($account);

                        // update last login date
                        $sql = sprintf('UPDATE users SET last_login = "%s", latest_login = "%s" WHERE id = "%s"',
                            $account['latest_login'], gmdate(DATE_FORMAT), $account['id'] );
                        $model->query($sql);

                        $auth = true;

                    }
                }
            }
        }

        return $auth;

    }


    /**
     *
     * @param type $user_id
     */
    protected function _registerAuthentication( $account ){

        if($account){

            $_SESSION['authenticated']      = true;
            $_SESSION['authentication_key'] = $this->getAuthenticationKey($account['email'], $account['id']);
            $_SESSION['domain_key']         = $this->getDomainKeyValue();
            $_SESSION['email']              = $account['email'];
            $_SESSION['username']           = $account['username'];
            $_SESSION['first_name']         = $account['first_name'];
            $_SESSION['last_name']          = $account['last_name'];
            $_SESSION['latest_login']       = gmdate(DATE_FORMAT);
            $_SESSION['last_login']         = $account['latest_login'];
            $_SESSION['user_id']            = $account['id'];

            // is this the very first login?
            $_SESSION['first_login']        = Date::isEmptyDate($account['latest_login']);

            // set an authentication cookie
            $this->_setAuthenticationCookie( 'authentication_key', $this->getAuthenticationKey($account['email'], $account['id']) );
            $this->_setAuthenticationCookie( 'user_id', $account['id'] );

            app()->refreshCage('session');

        }
    }


    /**
     *
     * @param type $key
     * @param type $value
     */
    protected function _setAuthenticationCookie( $key, $value ){
        setcookie( $key, $value, config()->get('authentication_cookie_expires'), '/' );
    }


    /**
     * Authenticates a user based on their cookie
     * @return boolean
     */
    protected function authenticateCookie(){

        if( cookie()->keyExists('user_id') ){

            $account = model()->open('users', cookie()->getInt('user_id'));
            if($account){
                $auth_key = $this->getAuthenticationKey($account['email'], $account['id']);
                if($auth_key === cookie()->getAlnum('authentication_key')){
                    $this->_registerAuthentication( $account );
                    return true;
                }
            }
        }
        return false;
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
     * @param string $email
     * @param string $user_id
     * @return string
     */
    public function getAuthenticationKey($email, $user_id){
        return sha1($email . $user_id);
    }


    /**
     * Returns the post-login redirect URL if it's been set.
     * @access public
     */
    public function postLoginRedirect(){
        $lred = $redirect = false;
        // custom redirects override default
        if(session()->isPath('custom_post-login_redirect')){
            $redirect = session()->getPath('custom_post-login_redirect');
            $lred = strtolower($redirect);
        } else {
            if(session()->isPath('post-login_redirect')){
                $redirect = session()->getPath('post-login_redirect');
                $lred = strtolower($redirect);
            }
        }
        if(strpos($lred, 'users/login') !== false || strpos($lred, 'users/authenticate') !== false){
            $redirect = false;
        }
        return (empty($redirect) ? router()->interfaceUrl() : $redirect);
    }


    /**
     * Returns whether or not the user is logged in
     * @return boolean
     * @access public
     */
    final public function determineUserAuthentication(){
        $auth_key = sha1(session()->getEmail('email') . session()->getInt('user_id'));
        if(app()->checkDbConnection()){
            if(
                session()->getInt('authenticated', false) &&
                session()->getAlnum('authentication_key') == $auth_key &&
                session()->getAlnum('domain_key') == $this->getDomainKeyValue()
            ){
                $this->authenticated = true;
            }
            elseif( $this->authenticateCookie() ){
                $this->authenticated = true;
            }
        }
        if($this->authenticated){
            $this->verifyAuthenticationAllowed();
        }
    }


    /**
     *
     */
    public function verifyAuthenticationAllowed(){
        // validate that this user is allowed to login. If this value changed between
        // last login, they may have a cookie that allows them to remain logged
        // in
        if(session()->isDigits('user_id')){
            $user = model()->open('users', session()->getInt('user_id'));
            if($user){
                if(!$user['allow_login']){
                    $this->logout();
                    router()->redirect('users/login');
                }
            } else {
                $this->logout();
                router()->redirect('users/login');
            }
        }
    }


    /**
     * Returns whether or not the user is logged in
     * @return boolean
     * @access public
     */
    final public function isLoggedIn(){
        return $this->authenticated;
    }


    /**
     * Returns true if the user has never logged in before
     * @param <type> $user_id
     * @return <type>
     */
    public function isFirstLogin(){
        return session()->getRaw('first_login');
    }


    /**
     * Checks if user has permissions to access an entire interface
     * @param string $interface
     * @param integer $user_id
     * @return boolean
     * @access public
     */
    public function userHasInterfaceAccess($interface = false, $user_id = false){

        $authenticated  = false;
        $interface      = $interface ? $interface : LOADING_SECTION;
//      $user_id        = $user_id ? $user_id : session()->getInt('user_id');

        if(IS_ADMIN){
            $authenticated = true;
        } else {

            if($this->isLoggedIn()){

                // Setting user id here, to ensure that the cookie authentication
                // has already restored the session
                $user_id = $user_id ? $user_id : session()->getInt('user_id');

                // first identify any groups this user belongs to
                $model = model()->open('user_group_link');
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

        $authenticated  = false;
        $module         = $module ? ucwords($module) : router()->module();
        $method         = $method ?: router()->method();
        $interface      = $interface ?: LOADING_SECTION;
        $user_id        = $user_id ?: session()->getInt('user_id');
        $module         = str_replace('_'.$interface, '', $module);

        if(IS_ADMIN || $this->allowAnonymous($module, $method, $interface)){
            $authenticated = true;
        } else {

            if($this->isLoggedIn() && $method != 'logout'){

                // first identify any groups this user belongs to
                if(isset($this->users_groups) && is_array($this->users_groups)){
                    $groups = $this->users_groups;
                } else {
                    $model = model()->open('user_group_link');
                    $model->select(array('group_id'));
                    $model->where('user_id', $user_id);
                    $groups = $model->results();
                    $groups = Utils::extract('/group_id', $groups);
                }

                foreach($this->permissions as $perm){
                    if(
                        ($perm['interface'] == ucwords($interface) || $perm['interface'] == '*') &&
                        ($perm['module'] == $module || $perm['module'] == '*') &&
                        ($perm['method'] == $method || $perm['method'] == '*') &&
                        (in_array($perm['group_id'], $groups) || $perm['user_id'] == $user_id)
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
        if(app()->isInstalled()){

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
        $this->authenticated = false;
        $_SESSION = array();
        session_destroy();
        setcookie( 'authentication_key', false, time() - 3600, '/' );
        setcookie( 'user_id', false, time() - 3600, '/' );
    }


    /**
     * Returns whether a user is in a group
     * @param mixed $group
     * @param integer $user_id
     * @return boolean
     */
    public function inGroup($group, $user_id = false){

        $user_id = $user_id ?: session()->getInt('user_id');

        $model = model()->open('user_group_link');
        $model->leftJoin('groups', 'id', 'group_id', array('name'));
        $model->where('user_id', $user_id);
        if(is_string($group)){
            $model->where('groups.name', $group);
        }
        if(is_int($group)){
            $model->where('groups.id', $group);
        }
        $groups = $model->results();
        return (boolean)$groups;

    }


    /**
     * Returns an array of groups the user is in
     * @param integer $user_id
     * @return boolean
     * @access public
     */
    public function usersGroups($user_id = false){
        $ingroups = array();
        if($user_id && model()->tableExists('user_group_link')){
            $model = model()->open('user_group_link');
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
        if($this->isLoggedIn() && model()->tableExists('user_group_link')){
            $model = model()->open('user_group_link');
            $model->where('user_id', session()->getInt('user_id'));
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
        if(app()->checkDbConnection() && model()->tableExists('users')){
            $model = model()->open('users');
            $accounts = $model->results();
            return $accounts ? count($accounts) : 0;
        } else {
            return 1;
        }
    }


    /**
     * Returns the default module for a specific user group
     * @access public
     */
    public function getUserDefaultModule(){
        $default = config()->get('default_module');
        if(app()->isInstalled() && model()->tableExists('config')){
            if($user_id = session()->getInt('user_id')){
                $groups = array_keys( $this->usersGroups($user_id) );
                $ug_defs = config()->get('usergroup_default_modules');
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
        $possible = "0123456789abcdfghjkmnpqrstvwxyz";
        $i = 0;
        while ($i < $length) {
            $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
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
        $model = model()->open('groups');
        $model->orderBy('name');
        $groups = $model->results();
        return $groups;
    }
}
?>