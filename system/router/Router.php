<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Manages urls and relation to our application
 * @package Aspen_Framework
 */
class Router extends Library {

	/**
	 * @var string $_selected_module Lists the currently selected module
	 * @access private
	 */
	private $_selected_module = false;

	/**
	 * @var string $_selected_method Lists the currently selected method
	 * @access private
	 */
	private $_selected_method = false;

	/**
	 * @var array $_selected_arguments An array of arguments to pass to a function
	 * @access private
	 */
	private $_selected_arguments = array();

	/**
	 * @var array $map Holds our array of mapped URL routes
	 * @access private
	 */
	private $map = array('module'=>false,'method'=>false,'bits'=>false);


	/**
	 * Constructor
	 * @return Router
	 * @access private
	 */
	public function __construct(){

		parent::__construct();

		// force use of ssl if required
		if($this->APP->config('force_https')){
			$this->redirectToUrl(str_replace(array("https", "http"), "https", $this->getFullUrl()));
		}

		// map the url elements and then identify the module/method to load
		$this->mapRequest();
		$this->loadRequestedPagePath();

	}


	/**
	 * Maps URI elements to an internal array - either GET or clean urls
	 * @access private
	 */
	private function mapRequest(){

		$bits = array();

		// if mod_rewrite enabled, and request doesn't look like a non-rewrite request
		if($this->APP->config('enable_mod_rewrite') && strpos($this->APP->params->server->getRaw('REQUEST_URI'), '.php?') === false){

			// we force the entire url as replacement because
			// an interface app name may be the same as a module
			// and we don't want to remove both, just one
			$replace = array($this->getApplicationUrl() . (LS != '' ? '/'.LS : '' ));
			if($this->getPath() != '/'){
				// try also to replace with app path minus the LS. If someone is masking the LS
				// then it may not be seen in the uri.
				$replace[] = $this->getApplicationUrl();
				$replace[] = $this->getPath();
			}
			$uri = $to_map = str_replace($replace, '', $this->getDomainUrl() . $this->APP->params->server->getRaw('REQUEST_URI'));
			$uri = explode('/', $uri);

			// If route mapping fails, then we need to parse the url for the default config
			if(!$this->applyRouteMap($to_map)){
				$this->map['module'] = isset($uri[1]) ? $this->stripQuery($uri[1]) : false;
				$this->map['method'] = isset($uri[2]) ? $this->stripQuery($uri[2]) : false;
			}

			// loop additional bits to pass to our arguments
			if(!$this->map['bits']){
				for($i = 3; $i < count($uri); $i++){
					$bits[] = $this->stripQuery($uri[$i]);
				}
				$this->map['bits'] = $bits;
			}
		} else {

			$this->map['module'] = $this->APP->params->get->getRaw('module');
			$this->map['method'] = $this->APP->params->get->getRaw('method');

			// loop additional bits to pass to our arguments
			$get = $this->APP->params->getRawSource('get');

			if(is_array($get)){
				foreach($get as $key => $value){
					if($key != 'module' && $key != 'method'){
						$bits[$key] = $this->stripQuery($this->APP->params->get->getRaw($key));
					}
				}
				$this->map['bits'] = $bits;
			}
		}

		// we need to remove any left over query string from malformed mod_rewrite query
		$this->map['method'] = $this->stripQuery($this->map['method']);

		// append interface to module
		if(!empty($this->map['module'])){
			$this->map['module'] .= (LOADING_SECTION != '' ? '_'.LOADING_SECTION : '');
		}
	}


	/**
	 * Removes any remaining query string from manual URI processing.
	 * @param string $str
	 * @return string
	 */
	private function stripQuery($str){
		return preg_replace('/\?(.*)/', '', $str);
	}


	/**
	 * Applies custom mapping routes to URLs. If a match is found, default is not applied.
	 * @param string $path
	 * @access private
	 * @return boolean
	 */
	private function applyRouteMap($path){

		$path			= $this->stripQuery($path);
		$routes			= $this->APP->config('routes');
		$map			= false;
		$matched_keys	= array();
		$matches		= array();

		if(!empty($path) && is_array($routes)){
			foreach($routes as $route => $map_to){
				if(isset($map_to['regex']) && $map_to['regex']){
					preg_match_all($route, $path, $matches);

					if(is_array($matches) && isset($matches[0]) && !empty($matches[0])){

						// Check for any variable matches to append
						for($a = 1; $a <= (count($matches)-1); $a++){

							// replace any placeholders in the map_to array
							foreach($map_to as $key => $ph){
								if($ph === '$'.$a){
									$matched_keys[] = $a;
									if(count($matches[$a]) == 1){
										$map_to[$key] = $matches[$a][0];
									} else {
										// we have an array of matches
										// @todo improve support for arrays: bug 1476
										if(isset($matches[$a][0])){
											$map_to[$key] = $matches[$a][0];
										}
									}
								}
							}

							// append any remaining matches as bits
							for($a = 1; $a <= (count($matches)-1); $a++){
								if(!in_array($a, $matched_keys)){
									if(!empty($matches[$a][0])){
										// @todo improve support for arrays: bug 1476
										$map_to['bits'][] = $matches[$a][0];
									}
								}
							}
						}

						$map = $map_to;
						break;
					}
				} else {
					if($path === $route){
						$map = $routes[$path];
					}
				}

				$this->APP->log->section('Routes Mapping');
				$this->APP->log->write($matches);

			}

			// proper map was found, set the information and return
			if(is_array($map)){
				$this->map['module']	=  isset($map['module']) ? $map['module'] : false;
				$this->map['method']	=  isset($map['method']) ? $map['method'] : false;
				$this->map['bits']		=  isset($map['bits']) ? $map['bits'] : false;
				return true;
			}
		}

		return false;

	}


	/**
	 * First checks for what path has been requested. Then, it
	 * checks for approval for that request (permissions, install, etc).
	 * @access private
	 */
	private function loadRequestedPagePath(){

		// requested
		$req_module = $this->identifyRequestedModuleForLoad();
		$req_method = $this->identifyRequestedMethodForLoad();

		$acc_module = false;
		$acc_method = false;

		// Check if anonymous access is allowed
		if(!$this->APP->user->allowAnonymous($req_module, $req_method, LS)){
			$acc_module = $this->identifyAcceptedModuleForLoad();
			$acc_method = $this->identifyAcceptedMethodForLoad();
		}

		// Override if access to requested path not allowed
		$acc_module = $acc_module ? $acc_module : $req_module;
		$acc_method = $acc_method ? $acc_method : $req_method;

		$this->_selected_module = ucfirst($acc_module);
		$this->_selected_method = $acc_method;

	}


	/**
	 * Identifies the module that has been requested, but not yet approved
	 * @return string
	 * @access private
	 */
	private function identifyRequestedModuleForLoad(){
		return $this->map['module'] ? $this->map['module'] : $this->APP->user->getUserDefaultModule();
	}


	/**
	 * Identifies the method that has been requested, but not yet approved
	 * @return string
	 * @access private
	 */
	private function identifyRequestedMethodForLoad(){
		return $this->map['method'] ? $this->map['method'] : $this->APP->config('default_method');
	}


	/**
	 * Identifies which module needs to be loaded
	 * @return string
	 * @access public
	 * @todo clean this up now that loadRequestedPagePath exists
	 */
	private function identifyAcceptedModuleForLoad(){

		if(strtolower(get_class($this->APP)) == "app"){

			if($this->APP->isInstalled() && $this->map['method'] != 'success' && $this->map['method'] != 'account' && !$this->APP->awaitingUpgrade()){

				// do a quick check to see if the user is logged in or not
				// we need to create our own auth check, as the user module is not loaded at this point
				if($this->APP->user->isLoggedIn()){

					$default = $this->map['module'] ? $this->map['module'] : false;

				} else {

					$default = 'Users' . (LOADING_SECTION ? '_' . LOADING_SECTION : false);

				}
			} else {

				$default = $this->APP->config('default_module_no_config');

			}
		} else {

			$default = get_class($this->APP);

		}

		return $default;

	}


	/**
	 * Identifies which method needs to be loaded
	 * @param string $default
	 * @return string
	 * @access private
	 * @todo clean this up now that loadRequestedPagePath exists
	 */
	private function identifyAcceptedMethodForLoad(){

		// do a basic login check as user module is not loaded at this point
		if($this->APP->user->isLoggedIn()){

			$default = $this->map['method'];
			$default = $default ? $default : $this->APP->config('default_method');

		}
		elseif($this->map['method'] == 'authenticate' || $this->map['method'] == 'forgot'){

			$default = $this->map['method'];

		} else {
			if($this->APP->isInstalled() && $this->map['method'] != 'success' && $this->map['method'] != 'account'){
				$default = 'login';
			} else {
				if($this->getSelectedModule() == "Install_Admin"){
					$default = $this->map['method'] ? $this->map['method'] : 'view';
				}
			}
		}

		return $default;

	}


	/**
	 * Returns the currently selected module
	 * @return string
	 * @access public
	 */
	public function getSelectedModule(){
		return $this->_selected_module;
	}


	/**
	 * Returns the currently selected method
	 * @return string
	 * @access public
	 */
	public function getSelectedMethod(){
		return $this->_selected_method;
	}


	/**
	 * Returns all current method arguments
	 * @return string
	 * @access private
	 */
	public function getSelectedArguments(){
		return $this->_arg;
	}


	/**
	 * Determines the parent of the current module, mainly for navigation purposes.
	 * @return string
	 * @access public
	 */
	public function getParentModule(){

		$module = $this->getSelectedModule();

		if(method_exists($this->APP->{$this->getSelectedModule()}, 'whosYourDaddy')){
			$daddy = $this->APP->{$this->getSelectedModule()}->whosYourDaddy();
			$module = empty($daddy) ? $module : $daddy;
		}

		return $module;
	}


	/**
	 * Loads the lanuage file for the current interface
	 * @access private
	 */
	public function loadInterfaceLanguage($interface = false){

		$lang 			= array();
		$lang_setting 	= $this->APP->config('language');

		// load the interface language library
		$lang_path	= INTERFACE_PATH . DS . 'language' . DS . $lang_setting . '.php';
		$this->APP->log->write('Seeking interface language library ' . $lang_path);
		if(file_exists($lang_path)){
			include($lang_path);
			$this->APP->log->write('Including interface language library ' . $lang_path);
			if(isset($lang[LS])){
				$lang = $lang[LS];
			}
		}

		$this->APP->template->loadLanguageTerms($lang);

	}


	/**
	 * Loads the lanuage file for the current module
	 * @access private
	 */
	public function loadModuleLanguage($module = false, $interface = false){

		$lang 			= array();
		$lang_setting 	= $this->APP->config('language');

		// load the module-specific language library
		$module = $this->cleanModule($module);
		$module_lang_path = $this->getModulePath($module, $interface).DS. 'language' . DS . $lang_setting.'.php';
		$this->APP->log->write('Seeking module language library ' . $module_lang_path);
		if(file_exists($module_lang_path)){
			include($module_lang_path);
			$this->APP->log->write('Including module language library ' . $module_lang_path);
			if(isset($lang[LS])){
				$lang = $lang[LS];
			}
		}

		$this->APP->template->loadLanguageTerms($lang);

	}


	/**
	 * Calls the module/method with arguments from the url
	 * @access public
	 */
	public function loadFromUrl(){

		// If user is logged in, but does not have access to this interface app
		if($this->APP->user->isLoggedIn() &&
				$this->getSelectedMethod() != 'login' && $this->getSelectedMethod() != 'autehenticate'
			){
			if(!$this->APP->user->userHasInterfaceAccess()){
				$this->APP->user->logout();
				$this->redirect('login', false, 'Users');
			}
		}

		// redirect if upgrade pending
		if($this->APP->user->isLoggedIn() && $this->getSelectedModule() != 'Install_Admin'){
			if($this->APP->awaitingUpgrade()){ $this->redirect('upgrade', false, 'Install'); }
		}

		if($this->getSelectedMethod() && $this->APP->user->userHasAccess()){

			// load the module language file
			if($this->APP->config('enable_languages')){
				$this->loadInterfaceLanguage();
				$this->loadModuleLanguage();
			}

			// set the function arguments for the method
			$i = 1;
			foreach($this->map['bits'] as $bit){
				$this->_selected_arguments[$i] = $bit;
				$i++;
			}

			$this->APP->log->write('Looking for Module: ' . $this->getSelectedModule() . '->' . $this->getSelectedMethod());

			/* this sucks balls, but I don't think you can call a function with an array as separate variables, like
			 * like imploding an array into separate function arguments  */
			if(isset($this->APP->{$this->getSelectedModule()})){
				if(method_exists($this->APP->{$this->getSelectedModule()}, $this->getSelectedMethod())){

					$this->APP->log->write('Running Module: ' . $this->getSelectedModule() . '->' . $this->getSelectedMethod());

					$this->APP->{$this->getSelectedModule()}->{$this->getSelectedMethod()}(
														$this->arg(1),
														$this->arg(2),
														$this->arg(3),
														$this->arg(4),
														$this->arg(5),
														$this->arg(6),
														$this->arg(7),
														$this->arg(8),
														$this->arg(9),
														$this->arg(10),
														$this->arg(11),
														$this->arg(12),
														$this->arg(13),
														$this->arg(14),
														$this->arg(15),
														$this->arg(16),
														$this->arg(17),
														$this->arg(18),
														$this->arg(19),
														$this->arg(20));

				} else { // method not found within module
					if($this->APP->config('log_error_on_404')){
						$this->APP->error->raise(2, 'Method ' . $this->getSelectedMethod() . ' does not exist in '
													 . $this->getSelectedModule(), __FILE__, __LINE__);
					}

					header("HTTP/1.0 404 Not Found");
					$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
					$this->APP->template->addView($this->APP->template->getTemplateDir().DS . '404.tpl.php');
					$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
					$this->APP->template->display();
					exit;
				}
			} else { // no module found
				if($this->APP->config('log_error_on_404')){
					$this->APP->error->raise(2, 'Module ' . $this->getSelectedModule() . ' does not exist.', __FILE__, __LINE__);
				}

				header("HTTP/1.0 404 Not Found");
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . '404.tpl.php');
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
				$this->APP->template->display();
				exit;
			}
		} else { // not authorized
			$this->loadModuleLanguage('Users', 'Admin');
			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
			$this->APP->template->addView($this->APP->template->getModuleTemplateDir('Users', 'Admin').DS . 'denied.tpl.php');
			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
			$this->APP->template->display();
			exit;
		}
	}


	/**
	 * Check for an argument index
	 * @param integer $index
	 * @return mixed
	 * @access private
	 */
	public function arg($index){
		return isset($this->_selected_arguments[$index]) ? $this->_selected_arguments[$index] : false;
	}


	/**
	 * Return an array of current arguments
	 * @return array
	 * @access public
	 */
	public function getMappedArguments(){
		return $this->map['bits'];
	}


	/**
	 * Returns a full URL path for the current page, query string and all
	 * @return string
	 * @access public
	 */
	public function getFullUrl(){

		$url = $this->getDomainUrl();
		$url .= $this->APP->params->server->getRaw('REQUEST_URI');

		return $url;

	}


	/**
	 * Returns the absolute url to the aspen installation path.
	 * @access public
	 * @return string
	 */
	public function getApplicationUrl(){

		$url = $this->getDomainUrl();
		$url .= $this->APP->config('application_url') ? $this->APP->config('application_url') : $this->getPath();
		return $url;

	}


	/**
	 * Returns the current port number
	 * @return integer
	 * @access public
	 */
	public function getPort(){
		return $this->APP->params->server->getInt('SERVER_PORT');
	}


	/**
	 * Returns the protocol / domain name only
	 * @access public
	 * @return string
	 */
	public function getDomainUrl(){

		$url = $this->getPort() == 443 ? 'https://' : 'http://';
		$url .= $this->APP->params->server->getRaw('SERVER_NAME');

		if($this->getPort() != 80 && $this->getPort() != 443){
			$url .= ':'.$this->getPort();
		}

		return $url;

	}


    /**
     * Attempts to return the post-domain name path to our application (minus interface)
     * @return string
     * @access public
     */
    public function getPath(){

        $adjusted_app_path = str_replace('\\', '/', APPLICATION_PATH);
        $adjusted_doc_root = str_replace('\\', '/', $this->APP->params->server->getRaw('DOCUMENT_ROOT'));

        $doc_strlen = strlen($adjusted_doc_root);
        if(substr($adjusted_doc_root, $doc_strlen - 1, $doc_strlen) == '/'){
            $adjusted_doc_root = substr($adjusted_doc_root, 0, $doc_strlen - 1);
        }

        // compare the first characters of both path vars,
        // if they don't match then we can't trust as true path
        $ar = substr($adjusted_app_path, 0, 7);
        $dr = substr($adjusted_doc_root, 0, 7);

        // attempt to determine path replacing doc root with domain
        if($adjusted_doc_root && $ar == $dr){
            $uri = str_replace($adjusted_doc_root, '', $adjusted_app_path);
        } else {
            // if no mod_rewrite, we need to handle the paths appropriately
            if($this->APP->config('enable_mod_rewrite') && strpos($this->APP->params->server->getRaw('REQUEST_URI'), '.php?') === false){

                $redirected = stripslashes($this->APP->params->get->getRaw('redirected'));
                $interface = LS ? LS : '';

                $replace = array();
                if(!empty($interface)){
                    $replace[] = '/'.$interface;
                }
                if(!empty($redirected)){
                    $replace[] = '/'.$redirected;
                }

                $uri = str_replace($replace, '', urldecode($this->APP->params->server->getRaw('REQUEST_URI')));

            } else {

                $no_qs_url = str_replace('?' . $this->APP->params->server->getRaw('QUERY_STRING'), '', $this->APP->params->server->getRaw('REQUEST_URI'));
                $interface = LS ? LS : '';
                $uri = str_replace(array("index.php", '/'.$interface), "", $no_qs_url);

            }
        }

        $uri = $uri == '/' ? false : $uri;
        return $uri;

    }


	/**
	 * Returns an absolute url to the current interface application.
	 * @param string $interface
	 * @return string
	 * @access public
	 */
	public function getInterfaceUrl($interface = false){
        $interface = $interface ? $interface : LS;
        return $this->getApplicationUrl() . (empty($interface) ? '' : '/' . $interface);
    }


	/**
	 * Returns the url to the file upoads directory
	 * @return string
	 * @access public
	 */
	public function getUploadsUrl(){
		$browser_url = $this->APP->config('upload_browser_path');
		if(!$browser_url){
			$browser_url = str_replace(APPLICATION_PATH, $this->getApplicationUrl(), $this->APP->config('upload_server_path'));
		}
		return $browser_url;
    }


	/**
	 * Returns the static content path if set in config, otherwise just interfaceUrl
	 * @return string
	 * @access public
	 */
	public function getStaticContentUrl($interface = false){

		if($this->APP->config('static_content_url')){
			return $this->APP->config('static_content_url');
		} else {

			$interface = $interface !== false ? $interface : LS;
			if(is_array($this->APP->config('interface_global_folder_replace'))){
				$replace = $this->APP->config('interface_global_folder_replace');
				if(array_key_exists($interface, $replace)){
					$interface = $replace[$interface];
				}
			}

			return $this->getInterfaceUrl($interface);
		}
	}


    /**
	 * Returns an absolute URL to the module folder
	 * @param string $module_name
	 * @return string
	 * @access public
	 */
	public function getModuleUrl($module_name = false){
		$module = $this->cleanModule($module_name);
		$registry = $this->APP->moduleRegistry(false, $module);
		return isset($registry->folder) ? $this->getApplicationUrl() . '/modules/' . $registry->folder : false;
	}


	/**
	 * Encodes a string for use in the url
	 * @param string $var
	 * @access public
	 * @return string
	 */
	public function encodeForRewriteUrl($var, $lc = false){
		$var = str_replace("?", "-question-", $var);
		$var = str_replace("/", "-slash-", $var);
		$var = str_replace("&", "-and-", $var);
		$var = str_replace(" ", "_", $var);
		$var = urlencode($var);
		$var = ($lc ? strtolower($var) : $var);
		return $var;
	}


	/**
	 * Decodes a string for use in the url
	 * @param string $var
	 * @access public
	 * @return string
	 */
	public function decodeForRewriteUrl($var){
		$var = urldecode($var);
		$var = str_replace("-question-", "?", $var);
		$var = str_replace("-slash-", "/", $var);
		$var = str_replace("-and-", "&", $var);
		$var = str_replace("_", " ", $var);
		return $var;
	}


	/**
	 * Returns the server path to our module
	 * @return string
	 * @access public
	 */
	public function getModulePath($module = false, $interface = false){

		$module = $this->cleanModule($module);
		$registry = $this->APP->moduleRegistry(false, $module, $interface);

		if(isset($registry->folder)){
			return MODULES_PATH . DS . $registry->folder;
		}
		return false;
	}


	/**
	 * Returns the module name without the interface in case it was provided
	 * that way.
	 *
	 * @param string $module
	 * @param string $interface
	 * @return string
	 */
	public function cleanModule($module = false, $interface = false){

		$module = $module ? $module : $this->getSelectedModule();
		$interface = $interface ? $interface : LOADING_SECTION;
		return str_replace('_'.ucwords($interface), '', $module);

	}


	/**
	 * Answers whether or not the user is in a specific location
	 * @param string $module
	 * @param string $method
	 * @return boolean
	 * @access public
	 */
	public function here($module = false, $method = false, $interface = false){

		$here = false;

		$selected_module = $this->getSelectedModule();
		$interface = $interface ? $interface : LOADING_SECTION;
		$module = $this->cleanModule($module);
		$module = $module . ($interface ? '_' . $interface  : false);

		if(isset($this->APP->{$this->getSelectedModule()})){
			if(method_exists($this->APP->{$this->getSelectedModule()}, 'whosYourDaddy')){
				$daddy = $this->APP->{$this->getSelectedModule()}->whosYourDaddy();
				$selected_module = empty($daddy) ? $selected_module : $daddy;
			}
		}

		if($module && $selected_module == $module){
			if($method){
				if($this->getSelectedMethod() == $method){
					$here = true;
				}
			} else {

				$here = true;

			}
		}

		return $here;

	}


	/**
	 * Sets the referring page for future reference
	 * @access public
	 */
	public function setReturnToReferrer(){
		if(strpos($this->getSelectedMethod(), 'ajax') === false){
			if($this->APP->params->server->getRaw('HTTP_REFERER')){
				$_SESSION['referring_page'] = $this->APP->params->server->getRaw('HTTP_REFERER');
			}
		}
	}


	/**
	 * Returns the user to the referring page when called
	 * @access public
	 */
	public function returnToReferrer(){
		$location = $this->APP->params->session->getRaw('referring_page', $this->APP->template->createUrl('view'));
		if(!empty($location)){
			$this->redirectToUrl($location);
		}
	}


	/**
	 * Redirects user to an inner-application module/method address.
	 * @param string $method
	 * @param array $bits
	 * @param string $module
	 * @access public
	 */
	public function redirect($method = false, $bits = false, $module = false, $interface = false){
		$this->redirectToUrl( $this->APP->template->createUrl($method, $bits, $module, $interface), false, true);
    }


	/**
	 * Redirects a user to any complete/absolute URL. Optionally, you may also
	 * provide status codes for an HTTP response as well as an exit, which discontinues
	 * executing following php code.
	 *
	 * @param string $url
	 * @param int $status
	 * @param boolean $exit
	 */
	public function redirectToUrl($url = false, $status = false, $exit = true){

		$tmp_ar = array('url'=>$url,'status'=>$status);
		$redirect = $this->APP->params->sanitize( $tmp_ar );

		if($redirect->isUri('url')){

			header("Location: ".$redirect->getRaw('url'));

			$codes = array(
					100 => 'Continue',
					101 => 'Switching Protocols',
					200 => 'OK',
					201 => 'Created',
					202 => 'Accepted',
					203 => 'Non-Authoritative Information',
					204 => 'No Content',
					205 => 'Reset Content',
					206 => 'Partial Content',
					300 => 'Multiple Choices',
					301 => 'Moved Permanently',
					302 => 'Found',
					303 => 'See Other',
					304 => 'Not Modified',
					305 => 'Use Proxy',
					307 => 'Temporary Redirect',
					400 => 'Bad Request',
					401 => 'Unauthorized',
					402 => 'Payment Required',
					403 => 'Forbidden',
					404 => 'Not Found',
					405 => 'Method Not Allowed',
					406 => 'Not Acceptable',
					407 => 'Proxy Authentication Required',
					408 => 'Request Time-out',
					409 => 'Conflict',
					410 => 'Gone',
					411 => 'Length Required',
					412 => 'Precondition Failed',
					413 => 'Request Entity Too Large',
					414 => 'Request-URI Too Large',
					415 => 'Unsupported Media Type',
					416 => 'Requested range not satisfiable',
					417 => 'Expectation Failed',
					500 => 'Internal Server Error',
					501 => 'Not Implemented',
					502 => 'Bad Gateway',
					503 => 'Service Unavailable',
					504 => 'Gateway Time-out'
				);

			$status = $redirect->getDigits('status');
			if ($status && isset($codes[$status]) && ($status >= 300 && $status < 400)) {
				$header = sprinf("HTTP/1.1 %s %s", $status, $codes[$status]);
				header($header);
			}

			if($exit){
				exit;
			}
		} else {
			$this->APP->error->raise(1, 'URL for redirect appears to be an invalid resource: '. $url, __FILE__, __LINE__);
		}
	}
}
?>