<?php
/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */


/**
 * Shortcut to return an instance of our original app
 * @return object
 */
function &template(){
	return app()->template;
}


/**
 * Shortcut for the language support method
 * @param string $type
 * @return string
 */
function text(){
	// pass any additional arguments straight to text
	$args = func_get_args();
	return call_user_func_array(array(app()->template,'text'),$args);
}


/**
 * This class manages our templates and loads them for display
 * @package Aspen_Framework
 */
class Template  {
	
	/**
	 * @var string Holds the page template name
	 * @access public
	 */
	public $page;
	
	/**
	 * @var string Holds the layout template name
	 * @access public
	 */
	public $layout = 'default';

	/**
	 * @var string Holds the display template of the page title
	 * @access public
	 */
	public $page_title = '{lang_title}';

	/**
	 * @var array An array of variables to pass through to the object
	 * @access private
	 */
	private $_data = array();

	/**
	 * @var array Holds custom css styles for printing in the header
	 * @access private
	 */
	private $_css_styles = array();

	/**
	 * @var array An array of css files queued for loading in the header
	 * @access private
	 */
	private $_load_css = array();

	/**
	 * @var array An array of javascript files queued for loading in the header
	 * @access private
	 */
	private $_load_js = array();

	/**
	 * @var array An array of javascript variables queued for loading in the header
	 * @access private
	 */
	private $_load_js_vars = array();

	/**
	 * @var array $lang Holds the current language values
	 * @access private
	 */
	private $terms;
	
	/**
	 * @var string CDTNL_CMT Holds the template string for a conditional comment
	 * @access private
	 */
	const CDTNL_CMT = '<!--[%s]>%s<![endif]-->';
	
	/**
	 * @var string CSS_ELM Holds the template string for a css include
	 * @access private
	 */
	const CSS_ELM = '<link rel="%s" href="%s" media="%s"%s />';
	
	/**
	 * @var string SCRIPT_ELM Holds the template string for a javascript include
	 * @access private
	 */
	const SCRIPT_ELM = '<script src="%s"></script>';
	
	
	/**
	 * Returns the layouts directory
	 * @return string
	 * @access public
	 */
	public function getLayoutDir(){
		return INTERFACE_PATH . DS. 'layouts';
	}


	/**
	 * Returns the template directory for our module
	 * @return string
	 * @access public
	 */
	public function getModuleTemplateDir($module = false, $interface = false){
		$orig_interface = $interface;
		$interface = $interface ? strtolower($interface) : LS;
		$module = router()->cleanModule($module);
        return router()->getModulePath($module, $orig_interface) . DS . 'templates' . ($interface == '' ? false : '_' . $interface);
    }
	
	
	/**
	 * Sorts and re-arranges css/javascript includes
	 */
	public function prepareMediaIncludes(){
		if(!empty($this->_load_css)){
			ksort($this->_load_css, SORT_STRING);
			// re-arrange to ensure all modules are second
			$m = array();
			$i = array();
			foreach($this->_load_css as $css){
				${$css['from']}[] = $css;
			}
			$this->_load_css = array_merge($i, $m);
		}
		// append any js files for loading
		if(!empty($this->_load_js)){
			ksort($this->_load_js, SORT_STRING);
			// re-arrange to ensure all modules are second
			$m = array();
			$i = array();
			foreach($this->_load_js as $js){
				${$js['from']}[] = $js;
			}
			$this->_load_js = array_merge($i, $m);
		}
	}


	/**
	 * Loads a header file for the currently loaded module, if that file exists
	 * @access public
	 */
	public function loadModuleHeader(){
		
		$this->prepareMediaIncludes();
		
		// append any css files for loading
		if(!empty($this->_load_css)){
			foreach($this->_load_css as $css){
				$file = $this->staticUrl($css);
				$link = sprintf(self::CSS_ELM, $css['rel'], $file, $css['media'], ($css['title'] ? ' title="'.$css['title'].'"' : ''));
				if(!empty($css['cdtnl_cmt'])){
					printf(self::CDTNL_CMT, $css['cdtnl_cmt']."\n", $link);
				} else {
					print $link."\n";
				}
			}
		}
		// append any custom css styles
		if(!empty($this->_css_styles)){
			print '<style>'."\n";
			foreach($this->_css_styles as $style){
				printf('%s { %s: %s }'."\n", $style['selector'], $style['attr'], $style['value']);
			}
			print '</style>'."\n";
		}
		// append any js files for loading
		if(app()->config('print_js_variables')){
			print '<script>'."\n";
			print 'var INTERFACE_URL = "'.router()->interfaceUrl().'";'."\n";
			if(is_array($this->_load_js_vars)){
				foreach($this->_load_js_vars as $var => $value){
					if(is_array($value)){
						print 'var '. strtoupper($var).' = '.json_encode($value).';'."\n";
					} else {
						print 'var '. strtoupper($var).' = "'.addslashes($value).'";'."\n";
					}
				}
			}
			print '</script>'."\n";
		}
		if(!empty($this->_load_js)){
			foreach($this->_load_js as $js){
				if($js['in'] == 'header'){
					$this->printJs($js);
				}
			}
		}
	}
	
	
	/**
	 * Loads a header file for the currently loaded module, if that file exists
	 * @access public
	 */
	public function loadModuleFooter(){
		$this->prepareMediaIncludes();
		if(!empty($this->_load_js)){
			foreach($this->_load_js as $js){
				if($js['in'] == 'footer'){
					$this->printJs($js);
				}
			}
		}
	}
	
	
	/**
	 * Prints the javascript file include
	 * @param type $js
	 */
	public function printJs($js){
		$file = $this->staticUrl($js);
		$link = sprintf(self::SCRIPT_ELM, $file);
		if(!empty($js['cdtnl_cmt'])){
			printf(self::CDTNL_CMT."\n", $js['cdtnl_cmt'], $link);
		} else {
			print $link."\n";
		}
	}


	/**
	 * CSS2 Supported types are: all, braille, embossed, handheld, print, projection, screen, speech, tty, tv
	 * http://www.w3.org/TR/CSS2/media.html
	 * @param array $args
	 * @access public
	 * @return string
	 */
	public function addCss($path, $args = false){

		$base = array(
					'url' => false,
					'file' => false,
					'rel' => 'stylesheet',
					'title' => false,
					'from' => 'm',
					'media' => 'all',
					'cdtnl_cmt' => '',
					'basepath' => false,
					'ext' => 'css',
					'interface'=>false
				);
		
		$path = $this->parseMediaFilePath($path);
		$base = array_merge($base, $path);
		$args = (is_array($args) ? array_merge($base, $args) : $base);

		// merge any incoming args and append the load array
		if(isset($args['order'])){
			array_splice($this->_load_css,$args['order'],0,array($args));
		} else {
			$this->_load_css[] = $args;
		}
	}


	/**
	 * Allows the user to add custom styles which will be printed with module header
	 * @param string $selector
	 * @param string $attr
	 * @param string $value
	 */
	public function setCssStyle($selector, $attr, $value){
		$this->_css_styles[] = array('selector'=>$selector,'attr'=>$attr,'value'=>$value);
	}


	/**
	 * Adds a javascript include to the header, from either the header template or the current module
	 * @param string $filename
	 * @param string $type
	 * @param string $basepath
	 * @access public
	 * @return string
	 */
	public function addJs($path, $args = false){
		
		$base = array(
					'url' => false,
					'file' => false,
					'from' => 'm',
					'cdtnl_cmt' => '',
					'basepath' => false,
					'ext' => 'js',
					'interface'=>false,
					'in' => 'header'
				);
		
		$path = $this->parseMediaFilePath($path);
		$base = array_merge($base, $path);
		$args = (is_array($args) ? array_merge($base, $args) : $base);

		// merge any incoming args and append the load array
		if(isset($args['order'])){
			array_splice($this->_load_js,$args['order'],0,array($args));
		} else {
			$this->_load_js[] = $args;
		}
	}


	/**
	 * Adds a javascript variable to the header
	 * @param string $key
	 * @param mixed $value
	 * @access public
	 */
	public function addJsVar($key, $value){
		$this->_load_js_vars[$key] = $value;
	}


	/**
	 * Base static url build for the addJs/addCss methods
	 * @param array $args
	 * @return string
	 * @access private
	 */
	private function staticUrl($args){

		$basepath = $filename = '';
		
		if($args['url']){
			$file = $args['url'];
		}
		else if($args['from'] == 'm'){
			$filename = $args['file'] ? $args['file'] : strtolower(router()->method()).'.'.$args['ext'];
			$basepath = $args['basepath'] ? $args['basepath'] : router()->moduleUrl() . '/'.$args['ext'];
			$file = $basepath . '/' . $filename;
		}
		else if($args['from'] == 'i'){
			$interface = !empty($args['interface']) ? $args['interface'] : false;
			$filename = $args['file'] ? $args['file'] : strtolower(LS).'.'.$args['ext'];
			$basepath = $args['basepath'] ? $args['basepath'] : router()->staticUrl($interface) . '/'.$args['ext'];
			$file = $basepath . '/' . $filename;
		}

		return $file;

	}
	
	
	/**
	 *
	 * @param type $path
	 * @return type 
	 */
	public function parseMediaFilePath($path){
		$base = array();
		$path_arr = explode('/', $path);
		if(is_array($path_arr)){
			$path_arr = array_reverse($path_arr);
			$base['file'] = (isset($path_arr[0]) ? $path_arr[0] : false);
			$base['from'] = (isset($path_arr[1]) ? 'i' : 'm');
			$base['interface'] = (isset($path_arr[1]) ? $path_arr[1] : false);
		} else {
			$base['file'] = $path;
		}
		return $base;
	}


	/**
	 * Sets the language text array from the router
	 * @param array $terms
	 * @access private
	 */
	public function loadLanguageTerms($terms = false){
		if(is_array($this->terms) && is_array($terms)){
			$this->terms = array_merge($this->terms, $terms);
		} else {
			$this->terms = $terms;
		}
	}


	/**
	 * Returns the text value for a key from the selected language
	 * @param string $key
	 * @return string
	 * @access public
	 */
	public function text($key){
		$text = isset($this->terms[$key]) ? $this->terms[$key] : '';
		// If non-empty, pass any additional arguments straight to sprintf
		if(!empty($text)){
			$args = func_get_args();
			if(count($args) > 1){
				$args[0] = $text;
				$text = call_user_func_array('sprintf',$args);
			}
		}
		return $text;
	}


	/**
	 * Assigns an array of data to pass through to the templates
	 * @param <type> $data
	 * @access public
	 */
	public function set($data){
		if(is_array($data)){
			$this->_data = array_merge($this->_data, $data);
		}
	}
	
	
	/**
	 * Display all templates that have been primed for output
	 * @param $data Array of data to be passed
	 * @access public
	 */
	public function page(){
	
		$page = $this->page ? $this->page : router()->method();
		if(router()->method() == 'add'){
			if(!file_exists($this->getModuleTemplateDir().DS.'add.tpl.php')){
				$page = 'edit';
			}
		}
		$template = $this->getModuleTemplateDir().DS.$page.'.tpl.php';
		
		if(file_exists($template) && strpos($template, APPLICATION_PATH) !== false){
			// pass through variables
			if(is_array($this->_data)){
				foreach($this->_data as $var => $value){
					$$var = $value;
				}
			}
			app()->log->write('Including template ' . $template);
			include($template);
		}
	}


	/**
	 * Display our layout that has been primed for output
	 * @param $data Array of data to be passed
	 * @access public
	 */
	public function display($data = false){

		$this->set($data);

		// if token auth on, we need to generate a token
		if(app()->config('require_form_token_auth')){
			$token = app()->security->generateFormToken();
		}
		
		$layout = $this->getLayoutDir().DS.$this->layout.'.tpl.php';

		if(file_exists($layout)){

			// pass through variables
			if(is_array($this->_data)){
				foreach($this->_data as $var => $value){
					$$var = $value;
				}
			}

			app()->log->write('Including layout ' . $layout);
			include($layout);

		}
		
		$this->resetTemplateQueue();

	}
	
	
	/**
	 * Set the current layout.
	 * @param string $layout 
	 */
	public function setLayout($layout){
		$this->layout = $layout;
	}
	
	
	/**
	 * Set the current page template.
	 * @param string $page 
	 */
	public function setPage($page){
		$this->page = $page;
	}


	/**
	 * Resets the template queue
	 * @access public
	 */
	public function resetTemplateQueue(){
		$this->_data			= array();
		$this->_load_css		= array();
		$this->_load_js			= array();
	}


	/**
	 * Adds a new link
	 * @param string $title
	 * @param string $module
	 * @param string $method
	 * @param string $text
	 */
	public function link($text, $path, $bits = false, $title = false, $add_class = false){
		
		$r = $this->parseNamespacePath($path);
		$title = $title ? $title : $text;

		$link = '';
		if(user()->userHasAccess($r['module'], $r['method'], $r['interface'])){

			// highlight the link if the user is at the page
			$class = ($add_class?$add_class:'');
			if($r['method'] == router()->method()
					&& ucwords($r['module']) == router()->cleanModule(router()->module())){
				$class = ' at';
			}

			$link = sprintf('<a href="%s" title="%s"%s>%s</a>',
								$this->xhtmlUrl($path, $bits),
								strip_tags($title),
								(!empty($class) ? ' class="'.$class.'"' : ''),
								$text
							);

		}

		return $link;

	}


	/**
	 * Returns class attribute if the user is at the selected location
	 * @param string $module
	 * @param string $method
	 * @param string $interface
	 * @return string
	 */
	public function at($path = false){
		return (router()->here($path) ? ' class="at"' : '');
	}
	
	
	/**
	 * Parses an interface/module/method path for the individual parts
	 * @param string $path
	 * @return string 
	 */
	public function parseNamespacePath($path = false){
		if($path){
			$path = explode('/',$path);
			$path = is_array($path) ? array_reverse($path) : $path;
		}
		$r['method'] = (is_array($path) && isset($path[0]) ? $path[0] : router()->method());
		$r['module'] = (is_array($path) && isset($path[1]) ? router()->cleanModule($path[1]) : strtolower(router()->cleanModule(router()->module())));
		$r['interface'] = (is_array($path) && isset($path[2]) ? strtolower($path[2]) : (LS != '' ? LS : ''));
		return $r;
	}
	
	
	/**
	 * Generates an interface/module/method path string
	 * @param string $method
	 * @param string $module
	 * @param string $interface
	 * @return string 
	 */
	public function getNamespacePath($method, $module = false, $interface = false){
		$path = '';
		if(!empty($interface)){
			$path .= $interface.'/';
		}
		if(!empty($module)){
			$path .= strtolower($module).'/';
		}
		$path .= $method;
		return $path;
	}


	/**
	 * Returns a URL using a module and method
	 * @param string $module
	 * @param array $bits Additional arguments to pass through the url
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function url($path = false, $bits = false){
		
		$r = $this->parseNamespacePath($path);
		$url = router()->interfaceUrl($r['interface']);
		
		// if mod rewrite/clean urls are off
		if(!app()->config('enable_mod_rewrite')){

			$url .= sprintf('/index.php?module=%s&method=%s', $r['module'], $r['method']);

			if(is_array($bits)){
				foreach($bits as $bit => $value){
					if(is_array($value)){
						foreach($value as $key => $val){
							$url .= '&' . $bit . '[' . $key . ']=' . urlencode($val);
						}
					} else {
						$url .= '&' . $bit . '=' . urlencode($value);
					}
				}
			}
		} else {

			// Determine if there are any routes that need to be used instead
			$routes = app()->config('routes');

			$route_mask = false;
			if(is_array($routes)){
				foreach($routes as $mask => $route){
					if(strtolower($route['module']) == strtolower($r['module']) && strtolower($route['method']) == strtolower($r['method'])){
						// if the interface is also set, it must match
						if(isset($route['interface'])){
							if(strtolower($route['interface']) == strtolower($r['interface'])){
								$route_mask = $mask;
								$url .= '/'.$route['uri'];
							}
						} else {
							$route_mask = $mask;
							$url .= '/'.$route['uri'];
						}
					}
				}
			}

			// Otherwise, just build it as normal
			if(!$route_mask){
				if($r['module'] != strtolower(app()->config('default_module')) || !empty($bits)){
					$url .= sprintf('/%s', $r['module']);
				}
				$url .= $r['method'] != app()->config('default_method') || is_array($bits) ? sprintf('/%s', $r['method']) : '';
			}

			if(is_array($bits)){
				foreach($bits as $bit => $value){
					if(is_array($value)){
						foreach($value as $key => $val){
							$url .= '/' . $bit . '[' . $key . ']=' . urlencode($val);
						}
					} else {
						$url .= '/' . urlencode($value);
					}
				}
			}
		}
		
		$url = rtrim($url, '/').'/'; // always use a trailing slash but never more

		return app()->config('lowercase_urls') ? strtolower($url) : $url;

	}


	/**
	 * Returns a properly-encoded URL using a module and method
	 * @param string $module
	 * @param array $bits Additional arguments to pass through the url
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function xhtmlUrl($path = false, $bits = false){
		return $this->encodeTextEntities($this->url($path, $bits));
	}


	/**
	 * Encodes entities that appear in text only, not html
	 * @param string $string
	 * @return string
	 * @access public
	 */
	public function encodeTextEntities($string){
		return str_replace("&", "&#38;", $string);
	}
	

	/**
	 * Returns a properly-encoded URL using a method
	 * @param string $method
	 * @param string $module
	 * @param string $interface
	 * @return string
	 * @access public
	 */
	public function action($path = false, $bits = false){
		if(router()->arg(1) && !$bits){
			$bits = array('id' => router()->arg(1));
		}
		return $this->xhtmlUrl($path, $bits);
	}


	/**
	 * Returns a properly-encoded URL using a module and method
	 * @param string $module
	 * @param array $bits Additional arguments to pass through the url
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function ajaxUrl($path = false, $bits = false){
		$orig_config = app()->config('enable_mod_rewrite');
		app()->setConfig('enable_mod_rewrite', false); // turn off rewrite urls
		$url = $this->url($path, $bits);
		app()->setConfig('enable_mod_rewrite', $orig_config); // turn them back to what they were
		return $url;
	}


	/**
	 * Creates a link to the current page with params, replacing any existing params
	 * @param array $bits
	 * @param string $method
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function selfLink($text, $bits = false, $path = false){
		$new_params = router()->getMappedArguments();
		// remove any options from the url that are in our new params
		if(is_array($bits) && count($bits)){
			foreach($bits as $key => $value){
				$new_params[$key] = $value;
			}
		}
		return $this->link($text, $path, $new_params);
	}


	/**
	 * Creates an xhtml valid url to the current page with params, replacing any existing params
	 * @param array $params
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function xhtmlSelfUrl($bits = false, $path = false){
		$new_params = router()->getMappedArguments();
		// remove any options from the url that are in our new params
		if(is_array($bits) && count($bits)){
			foreach($bits as $key => $value){
				$new_params[$key] = $value;
			}
		}
		if(!$path){
			$map = router()->getOriginalMap();
			$path = $map['module'].'/'.$map['method'];
		}
		return $this->xhtmlUrl($path, $new_params);
	}


	/**
	 * Creates a url to the current page with params, replacing any existing params
	 * @param array $params
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function selfUrl($bits = false, $path = false){
		$new_params = router()->getMappedArguments();
		// remove any options from the url that are in our new params
		if(is_array($bits) && count($bits)){
			foreach($bits as $key => $value){
				$new_params[$key] = $value;
			}
		}
		if(!$path){
			$map = router()->getOriginalMap();
			$path = $map['module'].'/'.$map['method'];
		}
		return $this->url($path, $new_params);
	}


	/**
	 * Creates a link for sorting a result set
	 * @param string $title
	 * @param string $location
	 * @param string $sort_by
	 * @return string
	 */
	public function sortLink($title, $location, $sort_by, $add_class = false){

		$base = $this->xhtmlSelfUrl();
		$sort = app()->prefs->getSort($location, $sort_by);

		// determine the sort direction
		$new_direction = $sort['sort_direction'] == "ASC" ? "DESC" : "ASC";
		
		// add class
		$add_class = $add_class ? ' '.$add_class : '';

		// determine the proper class, if any
		$class = 'sortable';
		if($sort['sort_by'] == $sort_by){
			$class = strtolower($new_direction);
		}

		// build proper url
		$url = $base.'?'.http_build_query(array(
									'sort_location'=>$location,
									'sort_by'=>$sort_by,
									'sort_direction'=>$new_direction), '', '&amp;');

		// create the link
		$html = sprintf('<a href="%s" title="%s" class="%s">%s</a>',
								$url,
								'Sort ' . $this->encodeTextEntities($title) . ' column ' . ($new_direction == 'ASC' ? 'ascending' : 'descending'),
								$class.$add_class,
								$this->encodeTextEntities($title)
							);

		return $html;

	}


	/**
	 * Generate a basic LI set of pagination links
	 * @param array $pages
	 * @return string
	 */
	public function paginateLinks($pages){
		
		$current_page	= $pages['current_page'];
		$per_page		= $pages['per_page'];
		$total_pages	= $pages['total_pages'];
		$url			= $this->selfUrl().'?';

		// build the html list item
		$html = '';

		if($total_pages > 1){

            $link_limit = app()->config('pagination_link_limit');
            $limit_balance = ceil(($link_limit / 2));

			// previous
			if($current_page > 1){
				$html .= sprintf('<li><a href="%spage=%d" class="page-prev">&laquo;</a></li>', $url, ($current_page-1));
			}

			// if we need to display the other page numbers
			if(app()->config('pagination_show_page_numbers')){

				// add in the first page
				$selected = $current_page == 1 ? ' at' : '';
				$html .= sprintf('<li class="page-first%s"><a href="%spage=%d">%3$d</a></li>', $selected, $url, 1);

				// if more than 15 results, show 15 page numbers closest to our current page
				$p      = 2;
				$limit  = $total_pages;
				if($total_pages > $link_limit){

					$p = $current_page;

					// start loop at 7 pages prior to current, if possible
					if($p > 7){
						$tmp_start = $p - $limit_balance;
						if($tmp_start > 0){
							$p = $tmp_start;
						}

						if($current_page >= ($total_pages - $limit_balance)){
							$p = $total_pages - $link_limit;
						}
					} else {
						$p = 2;
					}

					$p      = $p == 1 ? 2 : $p;
					$limit  = $p + $link_limit;
				}
				// add elipse if > 15 pages
				if($total_pages > $link_limit && $current_page > ($limit_balance+2)){
					$html .= '<li>&#8230;</li>';
				}
				// add in the numeric links
				while($p < $limit){
					$selected = $current_page == $p ? ' class="at"' : '';
					$html .= sprintf('<li%s><a href="%spage=%d">%3$d</a></li>', $selected, $url, $p);
					$p++;
				}
				// add elipsis if > 15 pages
				if($total_pages > $link_limit && $current_page < ($total_pages - $limit_balance)){
					$html .= '<li>&#8230;</li>';
				}
				// add in the last page
				$p = $total_pages;
				$selected = $current_page == $p ? ' at' : '';
				$html .= sprintf('<li class="page-last%s"><a href="%spage=%d">%3$d</a></li>', $selected, $url, $p);

			} else {
				// Otherwise, just add the current page
				$html .= sprintf('<li class="at"><a href="%spage=%d">%2$d</a></li>', $url, $current_page);
			}
			// next
			if($current_page < $total_pages){
				$html .= sprintf('<li><a href="%spage=%d" class="page-next">&raquo;</a></li>', $url, ($current_page+1));
			}
		}
		return $html;
	}


	/**
	 * Generates an html-safe element id using a string
	 * @param string $text
	 * @return string
	 * @access protected
	 */
	public function elemId($text){
		if(is_string($text)){
			return strtolower( preg_replace("/[^A-Za-z0-9_]/", "", str_replace(" ", "_", $text) ) );
		}
		return false;
	}


	/**
	 * Returns a body id of the module/method
	 * @return string
	 * @access public
	 */
	public function body_id(){
		return strtolower(router()->module().'_'.router()->method());
	}


	/**
	 * Returns a formatted page title for the current page
	 * @return string
	 * @access public
	 */
	public function page_title(){

		$module = router()->cleanModule(router()->module());
		$method = router()->method();

		$this->page_title = str_replace('{lang_title}', text(strtolower($module).':'.$method.':head-title'), $this->page_title);
		$this->page_title = str_replace('{module}', ucwords($module), $this->page_title);
		$this->page_title = str_replace('{method}', ucwords(router()->method()), $this->page_title);
		$this->page_title .= '&nbsp;&ndash;&nbsp;'.app()->config('application_name');

		return $this->page_title;

	}

	
	/**
	 * Returns a specific filter value from the GET params
	 * @param  string $key The key of the filter value you want
	 * @return mixed
	 * @access public
	 */
	public function filterValue($key = false){

		$filters = array();

		if(get()->isArray('filter')){
			$filters = get()->getArray('filter');
		} else {

			$sess_filters = session()->getArray('filter');

			if(isset($sess_filters[router()->module() . ':' . router()->method()])){
				$filters = $sess_filters[router()->module() . ':' . router()->method()];
			}
		}

		return isset($filters[$key]) ? $filters[$key] : false;

	}


	/**
	 *
	 * @param <type> $gmdate
	 * @param <type> $format
	 * @param <type> $timezone
	 * @return <type>
	 */
	public function pref_date($gmdate, $format = false, $timezone = false){

		$timezone	= $timezone ? $timezone : app()->config('timezone');
		$format		= $format ? $format : app()->config('date_format');

		// try to get a user timezone setting
		if($user_id = session()->getInt('user_id')){
			$timezone = app()->settings->getConfig('timezone', $user_id);
		}

		return Date::tzFormatDate($gmdate, $format, $timezone);

	}


	/**
	 * Generate an html SELECT element with values from a database
	 * @param string $selectTable
	 * @param string $selectField
	 * @param string $method
	 * @param string $orderby
	 * @param string $select_id
	 * @param string $where
	 * @return array
	 * @access public
	 */
	public function selectArray(
								$selectTable, $selectField, $method = "ENUM",
								$orderby = 'id', $select_id = false, $where = false){

		$return_select_array = array();

		if(!$select_id){
			$tbl = model()->open($selectTable);
			$select_id = $tbl->getPrimaryKey();
		}

		// If the type is ENUM, we'll get the possible values from
		// the database
		if($method == "ENUM"){
			$my_enums = app()->db->MetaColumns($selectTable, false);
			foreach($my_enums as $value){
				if($value->name == $selectField){
					foreach($value->enums as $choice){
						$choice = str_replace("'", "", $choice);
						$return_select_array[$choice] = $choice;
					}
				}
			}
		} else {

			// If the type is not enum, we'll get the possible values
			// from using a DISTINCT query
			$sql = "SELECT DISTINCT ".($select_id ? $select_id . ', ' : false)
						."$selectField FROM $selectTable $where ORDER BY $orderby";

			$getArray = model()->query($sql);

			if($getArray->RecordCount()){
				while($getArrayRow = $getArray->FetchRow()){
					if($select_id){
						$return_select_array[] = array($select_id=>$getArrayRow[$select_id], $selectField=>$getArrayRow[$selectField]);
					} else {
						$return_select_array[] = array('key'=>$getArrayRow[$selectField], $selectField=>$getArrayRow[$selectField]);
					}
				}
			}
		}

		return $return_select_array;

	}


	/**
	 * Prints out select box options using selectArray
	 * @param array $selectArray
	 * @param mixed $match_value
	 * @param boolean $prepend_blank
	 * @param string $blank_text
	 * @uses selectArray
	 * @access public
	 */
	public function optionArray($selectArray = false, $match_value = false, $prepend_blank = false, $blank_text = false){

		print $prepend_blank ? '<option value="">'.$blank_text.'</option>' . "\n" : '';

		if(is_array($selectArray)){
			foreach($selectArray as $key => $option){

				// if it's an array, we have values from DISTINCT
				if(is_array($option)){

					// get array keys
					$keys = array_keys($option);

					// if array has value different from text or not
					$value = empty($option[$keys[0]]) ? $option[$keys[1]] : $option[$keys[0]];

					// match
					$match = '';
					if(is_array($match_value)){
						$match = (in_array($value, $match_value) ? ' selected="selected"' : '');
					} else {
						$match = ($value == $match_value ? ' selected="selected"' : '');
					}

					printf('<option value="%s"%s>%s</option>' . "\n",
								$this->encodeTextEntities($value),
								$match,
								$this->encodeTextEntities($option[$keys[1]]));
				} else {

					// match
					$match = '';
					if(is_array($match_value)){
						$match = (in_array($key, $match_value) ? ' selected="selected"' : '');
					} else {
						$match = ($key == $match_value ? ' selected="selected"' : '');
					}

					printf('<option value="%s"%s>%s</option>' . "\n",
								$this->encodeTextEntities($key),
								$match,
								$this->encodeTextEntities($option));

				}

			}
		}
	}


	/**
	 * Hides values using html comments
	 * @param mixed $val
	 * @return string
	 * @access public
	 */
	public function htmlHide($val = false){
		return '<!--' . $val . '-->' . "\n";
	}


	/**
	 * Return an array of US states
	 * @return array
	 * @access public
	 */
	public function stateList(){

		return array(
				'AL'=>"Alabama",
                'AK'=>"Alaska",
                'AZ'=>"Arizona",
                'AR'=>"Arkansas",
                'CA'=>"California",
                'CO'=>"Colorado",
                'CT'=>"Connecticut",
                'DE'=>"Delaware",
                'DC'=>"District Of Columbia",
                'FL'=>"Florida",
                'GA'=>"Georgia",
                'HI'=>"Hawaii",
                'ID'=>"Idaho",
                'IL'=>"Illinois",
                'IN'=>"Indiana",
                'IA'=>"Iowa",
                'KS'=>"Kansas",
                'KY'=>"Kentucky",
                'LA'=>"Louisiana",
                'ME'=>"Maine",
                'MD'=>"Maryland",
                'MA'=>"Massachusetts",
                'MI'=>"Michigan",
                'MN'=>"Minnesota",
                'MS'=>"Mississippi",
                'MO'=>"Missouri",
                'MT'=>"Montana",
                'NE'=>"Nebraska",
                'NV'=>"Nevada",
                'NH'=>"New Hampshire",
                'NJ'=>"New Jersey",
                'NM'=>"New Mexico",
                'NY'=>"New York",
                'NC'=>"North Carolina",
                'ND'=>"North Dakota",
                'OH'=>"Ohio",
                'OK'=>"Oklahoma",
                'OR'=>"Oregon",
                'PA'=>"Pennsylvania",
                'RI'=>"Rhode Island",
                'SC'=>"South Carolina",
                'SD'=>"South Dakota",
                'TN'=>"Tennessee",
                'TX'=>"Texas",
                'UT'=>"Utah",
                'VT'=>"Vermont",
                'VA'=>"Virginia",
                'WA'=>"Washington",
                'WV'=>"West Virginia",
                'WI'=>"Wisconsin",
                'WY'=>"Wyoming");

	}
}
?>