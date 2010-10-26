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
	 * @var array $_loaded_languages Remembers which langs have already been loaded
	 * @access private
	 */
	private $_loaded_languages = array();

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
		if(app()->config('enable_mod_rewrite') && strpos(app()->server->getQueryString('REQUEST_URI'), '.php?') === false){

			// we force the entire url as replacement because
			// an interface app name may be the same as a module
			// and we don't want to remove both, just one
			$replace = array($this->appUrl() . (LS != '' ? '/'.LS : '' ));
			if($this->getPath() != '/'){
				// try also to replace with app path minus the LS. If someone is masking the LS
				// then it may not be seen in the uri.
				$replace[] = $this->appUrl();
				$replace[] = $this->getPath();
			}
			$uri = $to_map = str_replace($replace, '', $this->domainUrl() . app()->server->getQueryString('REQUEST_URI'));
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

			$this->map['module'] = app()->get->getElemId('module');
			$this->map['method'] = app()->get->getElemId('method');

			// loop additional bits to pass to our arguments
			$get = app()->params->getRawSource('get');

			if(is_array($get)){
				foreach($get as $key => $value){
					if($key != 'module' && $key != 'method'){
						$bits[$key] = $this->stripQuery(app()->get->getRaw($key));
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
		$routes			= app()->config('routes');
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

				app()->log->section('Routes Mapping');
				app()->log->write($matches);

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
		if(!app()->user->allowAnonymous($req_module, $req_method, LS)){
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
		return $this->map['module'] ? $this->map['module'] : app()->user->getUserDefaultModule();
	}


	/**
	 * Identifies the method that has been requested, but not yet approved
	 * @return string
	 * @access private
	 */
	private function identifyRequestedMethodForLoad(){
		return $this->map['method'] ? $this->map['method'] : app()->config('default_method');
	}


	/**
	 * Identifies which module needs to be loaded
	 * @return string
	 * @access public
	 * @todo clean this up now that loadRequestedPagePath exists
	 */
	private function identifyAcceptedModuleForLoad(){

		if(strtolower(get_class(app())) == "app"){

			if(app()->isInstalled()){

				// do a quick check to see if the user is logged in or not
				// we need to create our own auth check, as the user module is not loaded at this point
				if(app()->user->isLoggedIn()){

					$default = $this->map['module'] ? $this->map['module'] : false;

				} else {

					$default = 'Users' . (LOADING_SECTION ? '_' . LOADING_SECTION : false);

				}
			} else {

				$default = app()->config('default_module_no_config');

			}
		} else {

			$default = get_class(app());

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
		
		$default = 'view';

		// do a basic login check as user module is not loaded at this point
		if(app()->user->isLoggedIn()){

			$default = $this->map['method'];
			$default = $default ? $default : app()->config('default_method');

		}
		elseif($this->map['method'] == 'authenticate' || $this->map['method'] == 'forgot'){

			$default = $this->map['method'];

		} else {
			if(app()->isInstalled()){
				$default = 'login';
			}
		}

		return $default;

	}


	/**
	 * Returns the currently selected module
	 * @return string
	 * @access public
	 */
	public function module(){
		return $this->_selected_module;
	}


	/**
	 * Returns the currently selected method
	 * @return string
	 * @access public
	 */
	public function method(){
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

		$module = $this->module();

		if(method_exists(app()->{$this->module()}, 'whosYourDaddy')){
			$daddy = app()->{$this->module()}->whosYourDaddy();
			$module = empty($daddy) ? $module : $daddy;
		}

		return $module;
	}


	/**
	 * Loads the lanuage file for the current interface
	 * @access private
	 */
	public function loadInterfaceLanguage($interface = false){

		$languages 		= array();
		$lang_setting 	= app()->config('language');

		// load the interface language library
		$path = $interface ? APPLICATION_PATH.DS.strtolower($interface) : INTERFACE_PATH;
		$lang_path	= $path . DS . 'language' . DS . $lang_setting . '.php';
		app()->log->write('Seeking interface language library ' . $lang_path);
		if(file_exists($lang_path)){
			include($lang_path);
			app()->log->write('Including interface language library ' . $lang_path);
			if(isset($lang[LS])){
				$languages = array_merge($languages, $lang[LS]);
			}
			if(isset($lang['*'])){
				$languages = array_merge($languages, $lang['*']);
			}
		}

		app()->template->loadLanguageTerms($languages);

	}


	/**
	 * Loads the lanuage file for the current module
	 * @access private
	 */
	public function loadModuleLanguage($module = false, $interface = false){

		$languages 		= array();
		$lang_setting 	= app()->config('language');

		// load the module-specific language library
		if(!in_array($module.'_'.$interface, $this->_loaded_languages)){
			$module = $this->cleanModule($module);
			$module_lang_path = $this->getModulePath($module, $interface).DS. 'language' . DS . $lang_setting.'.php';
			app()->log->write('Seeking module language library ' . $module_lang_path);
			if(file_exists($module_lang_path)){
				include($module_lang_path);
				app()->log->write('Including module language library ' . $module_lang_path);
				if(isset($lang[LS])){
					$languages = array_merge($languages, $lang[LS]);
				}
				if(isset($lang['*'])){
					$languages = array_merge($languages, $lang['*']);
				}
			}

			$this->_loaded_languages[] = $module.'_'.$interface;

			app()->template->loadLanguageTerms($languages);
		}
	}


	/**
	 * Loads all language files for user-created libraries.
	 * @access private
	 */
	protected function loadLibraryLanguages(){

		$langs = array();

		$libs = app()->getLoadedLibraries();
		foreach($libs as $lib){
			if(isset($lib['module']) && is_object($lib['module'])){
				$langs[(string)$lib['module']->classname] = (string)$lib['module']->classname;
			}
		}
		if(!empty($langs)){
			foreach($langs as $module){
				$this->loadModuleLanguage($module);
			}
		}
		return $langs;
	}


	/**
	 * Calls the module/method with arguments from the url
	 * @access public
	 */
	public function loadFromUrl(){

		// If user is logged in, but does not have access to this interface app
		if(app()->user->isLoggedIn() &&
				$this->method() != 'login' && $this->method() != 'autehenticate'
			){
			if(!app()->user->userHasInterfaceAccess()){
				app()->user->logout();
				$this->redirect('login', false, 'Users');
			}
		}

		if($this->method() && app()->user->userHasAccess()){

			// load the module language file
			if(app()->config('enable_languages')){
				$this->loadInterfaceLanguage();
				$this->loadLibraryLanguages();
				$this->loadModuleLanguage();
			}

			// set the function arguments for the method
			$i = 1;
			foreach($this->map['bits'] as $bit){
				$this->_selected_arguments[$i] = $bit;
				$i++;
			}

			app()->log->write('Looking for Module: ' . $this->module() . '->' . $this->method());

			if(isset(app()->{$this->module()})){
				if(method_exists(app()->{$this->module()}, $this->method())){

					app()->log->write('Running Module: ' . $this->module() . '->' . $this->method());

					// Call the actual class method for our current page, and pass all arguments to it
					call_user_func_array(array(app()->{$this->module()}, $this->method()), $this->_selected_arguments);

				} else { // method not found within module
					if(app()->config('log_error_on_404')){
						app()->error->raise(2, 'Method ' . $this->method() . ' does not exist in '
													 . $this->module(), __FILE__, __LINE__);
					}

					header("HTTP/1.0 404 Not Found");
					app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
					app()->template->addView(app()->template->getTemplateDir().DS . '404.tpl.php');
					app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
					app()->template->display();
					exit;
				}
			} else { // no module found
				if(app()->config('log_error_on_404')){
					app()->error->raise(2, 'Module ' . $this->module() . ' does not exist.', __FILE__, __LINE__);
				}

				header("HTTP/1.0 404 Not Found");
				app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
				app()->template->addView(app()->template->getTemplateDir().DS . '404.tpl.php');
				app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
				app()->template->display();
				exit;
			}
		} else { // not authorized
			$this->loadModuleLanguage('Users', 'Admin');
			app()->template->page_title = text('users:denied:head-title');
			app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
			app()->template->addView(app()->template->getModuleTemplateDir('Users', 'Admin').DS . 'denied.tpl.php');
			app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
			app()->template->display();
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
	public function fullUrl(){

		$url = $this->domainUrl();
		$url .= app()->server->getQueryString('REQUEST_URI');
		$url = strip_tags(urldecode($url));

		return $url;

	}


	/**
	 * Returns the absolute url to the aspen installation path.
	 * @access public
	 * @return string
	 */
	public function appUrl(){

		$url = $this->domainUrl();
		$url .= app()->config('application_url') ? app()->config('application_url') : $this->getPath();
		return $url;

	}


	/**
	 * Returns the current port number
	 * @return integer
	 * @access public
	 */
	public function port(){
		return app()->server->getInt('SERVER_PORT');
	}


	/**
	 * Returns the protocol / domain name only
	 * @access public
	 * @return string
	 */
	public function domainUrl(){

		$url = $this->port() == 443 ? 'https://' : 'http://';
		$url .= app()->server->getServerName('SERVER_NAME');

		if($this->port() != 80 && $this->port() != 443){
			$url .= ':'.$this->port();
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
        $adjusted_doc_root = str_replace('\\', '/', app()->server->getPath('DOCUMENT_ROOT'));

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
            if(app()->config('enable_mod_rewrite') && strpos(app()->server->getQueryString('REQUEST_URI'), '.php?') === false){

                $redirected = stripslashes(app()->get->getQueryString('redirected'));
                $interface = LS ? LS : '';

                $replace = array();
                if(!empty($interface)){
                    $replace[] = '/'.$interface;
                }
                if(!empty($redirected)){
                    $replace[] = '/'.$redirected;
                }

                $uri = str_replace($replace, '', urldecode(app()->server->getQueryString('REQUEST_URI')));
				$uri = $this->stripQuery($uri);

            } else {

                $no_qs_url = str_replace('?' . app()->server->getQueryString('QUERY_STRING'), '', app()->server->getQueryString('REQUEST_URI'));
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
	public function interfaceUrl($interface = false){
        $interface = $interface ? $interface : LS;
        return $this->appUrl() . (empty($interface) ? '' : '/' . $interface);
    }


	/**
	 * Returns the url to the file upoads directory
	 * @return string
	 * @access public
	 */
	public function uploadsUrl(){
		$browser_url = app()->config('upload_browser_path');
		if(!$browser_url){
			$browser_url = str_replace(APPLICATION_PATH, $this->appUrl(), app()->config('upload_server_path'));
		}
		return $browser_url;
    }


	/**
	 * Returns the static content path if set in config, otherwise just interfaceUrl
	 * @return string
	 * @access public
	 */
	public function staticUrl($interface = false){

		if(app()->config('static_content_url')){
			return app()->config('static_content_url');
		} else {

			$interface = $interface !== false ? $interface : LS;
			if(is_array(app()->config('interface_global_folder_replace'))){
				$replace = app()->config('interface_global_folder_replace');
				if(array_key_exists($interface, $replace)){
					$interface = $replace[$interface];
				}
			}

			return $this->interfaceUrl($interface);
		}
	}


    /**
	 * Returns an absolute URL to the module folder
	 * @param string $module_name
	 * @return string
	 * @access public
	 */
	public function moduleUrl($module_name = false){
		$module = $this->cleanModule($module_name);
		$registry = app()->moduleRegistry(false, $module);
		return isset($registry->folder) ? $this->appUrl() . '/modules/' . $registry->folder : false;
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
		$registry = app()->moduleRegistry(false, $module, $interface);

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

		$module = $module ? $module : $this->module();
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

		$selected_module = $this->module();
		$interface = $interface ? $interface : LOADING_SECTION;
		$module = $this->cleanModule($module);
		$module = $module . ($interface ? '_' . $interface  : false);

		if(isset(app()->{$this->module()})){
			if(method_exists(app()->{$this->module()}, 'whosYourDaddy')){
				$daddy = app()->{$this->module()}->whosYourDaddy();
				$selected_module = empty($daddy) ? $selected_module : $daddy;
			}
		}

		if($module && $selected_module == $module){
			if($method){
				if(is_array($method)){
					$here = in_array($this->method(), $method);
				} else {
					$here = $this->method() == $method;
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
		if(strpos($this->method(), 'ajax') === false){
			if(app()->server->getUri('HTTP_REFERER')){
				$_SESSION['referring_page'] = app()->server->getUri('HTTP_REFERER');
				app()->refreshCage('session');
			}
		}
	}


	/**
	 * Returns the user to the referring page when called
	 * @access public
	 */
	public function returnToReferrer(){
		$location = app()->session->getUri('referring_page', app()->template->url('view'));
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
		$this->redirectToUrl( app()->template->url($method, $bits, $module, $interface), false, true);
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
		$redirect = Peregrine::sanitize( $tmp_ar );

		if($redirect->isUri('url')){
			header("Location: ".$redirect->getUri('url'));
			$status = $redirect->getDigits('status');
			$this->header_code($status);
			if($exit){
				exit;
			}
		} else {
			app()->error->raise(1, 'URL for redirect appears to be an invalid resource: '. $url, __FILE__, __LINE__);
		}
	}


	/**
	 * Delivers a header code to the browser
	 * @param integer $status
	 */
	public function header_code($status = false){

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

		if ($status && isset($codes[$status]) && ($status >= 300 && $status < 400)) {
			$header = sprinf("HTTP/1.1 %s %s", $status, $codes[$status]);
			header($header);
		}
	}
}
?>