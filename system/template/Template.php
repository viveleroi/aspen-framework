<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * This class manages our templates and loads them for display
 * @package Aspen_Framework
 */
class Template extends Library {

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
	private $_load_css;

	/**
	 * @var array An array of javascript files queued for loading in the header
	 * @access private
	 */
	private $_load_js;

	/**
	 * @var array $_load_templates An array of templates queued for display
	 * @access private
	 */
	private $_load_templates;

	/**
	 * @var array $lang Holds the current language values
	 * @access private
	 */
	private $terms;


	/**
	 * Returns the template directory
	 * @return string
	 * @access public
	 */
	public function getTemplateDir(){
		return INTERFACE_PATH . DS. 'templates';
	}


	/**
	 * Returns the template directory for our module
	 * @return string
	 * @access public
	 */
	public function getModuleTemplateDir($module = false, $interface = false){
		$orig_interface = $interface;
		$interface = $interface ? strtolower($interface) : LS;
		$module = app()->router->cleanModule($module);
        return app()->router->getModulePath($module, $orig_interface) . DS . 'templates' . ($interface == '' ? false : '_' . $interface);
    }


	/**
	 * Loads a header file for the currently loaded module, if that file exists
	 * @access public
	 */
	public function loadModuleHeader(){

		$cdtnl_cmt = '<!--[%s]>%s<![endif]-->';

		// append any css files for loading
		if(!empty($this->_load_css)){

			ksort($this->_load_css, SORT_STRING);

			// re-arrange to ensure all modules are second
			$m = array();
			$i = array();
			foreach($this->_load_css as $css){
				${$css['from']}[] = $css;
			}
			$this->_load_css = array_merge($i, $m);

			$css_html_elm = '<link rel="%s" href="%s" type="text/css" media="%s"%s />';

			foreach($this->_load_css as $css){
				$file = $this->staticUrl($css);
				$link = sprintf($css_html_elm, $css['rel'], $file, $css['mediatype'], ($css['title'] ? ' title="'.$css['title'].'"' : ''));

				if(!empty($css['cdtnl_cmt'])){
					printf($cdtnl_cmt, $css['cdtnl_cmt'], $link);
				} else {
					print $link;
				}

				print "\n";

			}
		}

		// append any custom css styles
		if(!empty($this->_css_styles)){
			print '<style type="text/css">'."\n";
			foreach($this->_css_styles as $style){
				printf('%s { %s: %s }'."\n", $style['selector'], $style['attr'], $style['value']);
			}
			print '</style>'."\n";
		}

		// append any js files for loading
		if(!empty($this->_load_js)){

			// print some js globals
			if(app()->config('print_js_variables')){
				print '<script type="text/javascript">'."\n";
				print 'var INTERFACE_URL = "'.app()->router->interfaceUrl().'";'."\n";
				print '</script>'."\n";
			}

			ksort($this->_load_js, SORT_STRING);

			// re-arrange to ensure all modules are second
			$m = array();
			$i = array();
			foreach($this->_load_js as $js){
				${$js['from']}[] = $js;
			}
			$this->_load_js = array_merge($i, $m);

			$js_html_elm = '<script type="text/javascript" src="%s"></script>'."\n";

			foreach($this->_load_js as $js){
				$file = $this->staticUrl($js);
				$link = sprintf($js_html_elm, $file);

				if(!empty($js['cdtnl_cmt'])){
					printf($cdtnl_cmt, $js['cdtnl_cmt'], $link);
				} else {
					print $link;
				}
			}
		}

		// include any header templates (header.tpl.php)
		$path = $this->getModuleTemplateDir().DS . 'header.tpl.php';

		if(file_exists($path)){
			define('MODULE_HEADER_TPL_PATH', $path);
			include(MODULE_HEADER_TPL_PATH);
		} else {
			define('MODULE_HEADER_TPL_PATH', '');
		}
	}


	/**
	 * CSS2 Supported types are: all, braille, embossed, handheld, print, projection, screen, speech, tty, tv
	 * http://www.w3.org/TR/CSS2/media.html
	 * @param array $args
	 * @access public
	 * @return string
	 */
	public function addCss($args){

		$base = array(
					'file' => false,
					'rel' => 'stylesheet',
					'title' => false,
					'from' => 'm',
					'mediatype' => 'all',
					'cdtnl_cmt' => '',
					'basepath' => false,
					'ext' => 'css'
				);

		$sort_key = isset($args['order']) ? $args['order'] : $args['file'];

		// merge any incoming args and append the load array
		$this->_load_css[$sort_key] = (is_array($args) ? array_merge($base, $args) : $base);

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
	public function addJs($args){

		$base = array(
					'file' => false,
					'from' => 'm',
					'cdtnl_cmt' => '',
					'basepath' => false,
					'ext' => 'js'
				);

		$sort_key = isset($args['order']) ? $args['order'] : $args['file'];

		// merge any incoming args and append the load array
		$this->_load_js[$sort_key] = (is_array($args) ? array_merge($base, $args) : $base);

	}


	/**
	 * Base static url build for the addJs/addCss methods
	 * @param array $args
	 * @return string
	 * @access private
	 */
	private function staticUrl($args){

		$basepath = $filename = '';

		if($args['from'] == 'm'){
			$filename = $args['file'] ? $args['file'] : strtolower(app()->router->method()).'.'.$args['ext'];
			$basepath = $args['basepath'] ? $args['basepath'] : app()->router->moduleUrl() . '/'.$args['ext'];
		}

		if($args['from'] == 'i'){
			$interface = isset($args['interface']) ? $args['interface'] : false;
			$filename = $args['file'] ? $args['file'] : strtolower(LS).'.'.$args['ext'];
			$basepath = $args['basepath'] ? $args['basepath'] : app()->router->staticUrl($interface) . '/'.$args['ext'];
		}

		return $file = $basepath . '/' . $filename;

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
	public function display($data = false){

		$this->set($data);

		if(is_array($this->_load_templates)){

			// if token auth on, we need to generate a token
			if(app()->config('require_form_token_auth')){
				$token = app()->security->generateFormToken();
			}

			$cache = false;

			// if cache of template enabled
			if(app()->config('enable_cache') && app()->config('cache_template_output')){

				// if we can find any existing cache of this page
				if($cache = app()->cache->getData($this->selfUrl())){
					app()->log->write('Returning templates from cache.');
					print $cache;
				} else {

					app()->log->write('Beginning output buffering, to capture template html for cache.');

					// begin collecting output
					ob_start();

				}
			}

			// if no cache enabled or found, display the templates
			if(!$cache){
				foreach($this->_load_templates as $template){
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
			}

			// if cache enabled and cache file not found, save it
			if(app()->config('enable_cache') && app()->config('cache_template_output') && !$cache){

				app()->cache->put($this->selfUrl(), ob_get_contents());
				ob_end_flush();

				app()->log->write('Saved output contents to cache, and stopped output buffering.');

			}
		}

		$this->resetTemplateQueue();

	}


	/**
	 * Adds a template to the display stack, so when the display
	 * function is called, the templates will be output in the
	 * order they were added.
	 * @param string $template
	 * @access public
	 */
	public function addView($template){
		$this->_load_templates[] = $template;
	}


	/**
	 * Resets the template queue
	 * @access public
	 */
	public function resetTemplateQueue(){
		$this->_data			= array();
		$this->_load_templates	= array();
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
	public function link($text, $method = false, $bits = false, $module = false, $title = false, $interface = false ){

		// set values, or use default if false.
		$method = $method ? $method : app()->router->method();
		$module = app()->router->cleanModule($module);
		$interface = $interface ? $interface : LOADING_SECTION;
		$interface = empty($interface) ? false : $interface;
		$title = $title ? $title : $text;

		$mi = empty($interface) ? $module : $module.'_'.$interface;

		$link = '';
		if(app()->user->userHasAccess($module, $method, $interface)){

			$class = false;

			// highlight the link if the user is at the page
			if($method == app()->router->method()
					&& $mi == app()->router->module()){
				$class = true;
			}

			$link = sprintf(
					'<a href="%s" title="%s"%s>%s</a>',
								$this->xhtmlUrl($method, $bits, $module, $interface),
								$this->encodeTextEntities($title),
								($class ? ' class="at"' : ''),
								$this->encodeTextEntities($text)
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
	public function at($module = false, $method = false, $interface = false){
		return (app()->router->here($module, $method, $interface) ? ' class="at"' : '');
	}


	/**
	 * Returns a URL using a module and method
	 * @param string $module
	 * @param array $bits Additional arguments to pass through the url
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function url($method = false, $bits = false, $module = false, $interface = false){

		// begin url with absolute url to this app
		$interface = strtolower( $interface ? $interface : (LOADING_SECTION != '' ? LOADING_SECTION : '') );
		$url = app()->router->interfaceUrl($interface);

		$method = $method ? $method : app()->router->method();
		$module = app()->router->cleanModule($module);

		// if mod rewrite/clean urls are off
		if(!app()->config('enable_mod_rewrite')){

			$url .= sprintf('/index.php?module=%s&method=%s', $module, $method);

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
					if(
						strtolower($route['module']) == strtolower($module) &&
						strtolower($route['method']) == strtolower($method) &&
					    (isset($route['interface']) && strtolower($route['interface']) == strtolower($interface))
					 ){
						$route_mask = $mask;
						$url .= $mask;
					}
				}
			}

			// Otherwise, just built it as normal
			if(!$route_mask){

				$url .= sprintf('/%s', $module);
				$url .= $method != app()->config('default_method') || is_array($bits) ? sprintf('/%s', $method) : '';

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
		}

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
	public function xhtmlUrl($method = false, $bits = false, $module = false, $interface = false){
		return $this->encodeTextEntities($this->url($method, $bits, $module, $interface));
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
	 * @return string
	 * @access public
	 */
	public function action($method = false, $module = false){
		$bits = false;
		if(app()->router->arg(1)){
			$bits = array('id' => app()->router->arg(1));
		}
		return $this->xhtmlUrl($method, $bits, $module);
	}


	/**
	 * Returns a properly-encoded URL using a module and method
	 * @param string $module
	 * @param array $bits Additional arguments to pass through the url
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function ajaxUrl($method = false, $bits = false, $module = false, $interface = false){
		$orig_config = app()->config('enable_mod_rewrite');
		app()->setConfig('enable_mod_rewrite', false); // turn off rewrite urls
		$url = $this->url($method, $bits, $module, $interface);
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
	public function selfLink($text, $bits = false, $method = false){

		$new_params = app()->router->getMappedArguments();

		// remove any options from the url that are in our new params
		if(is_array($bits) && count($bits)){
			foreach($bits as $key => $value){
				$new_params[$key] = $value;
			}
		}

		return $this->link($text, false, $new_params, $method);

	}


	/**
	 * Creates an xhtml valid url to the current page with params, replacing any existing params
	 * @param array $params
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function xhtmlSelfUrl($bits = false, $method = false){

		$new_params = app()->router->getMappedArguments();

		// remove any options from the url that are in our new params
		if(is_array($bits) && count($bits)){
			foreach($bits as $key => $value){
				$new_params[$key] = $value;
			}
		}

		return $this->xhtmlUrl($method, $new_params);

	}


	/**
	 * Creates a url to the current page with params, replacing any existing params
	 * @param array $params
	 * @param string $method
	 * @return string
	 * @access public
	 */
	public function selfUrl($bits = false, $method = false){

		$new_params = app()->router->getMappedArguments();

		// remove any options from the url that are in our new params
		if(is_array($bits) && count($bits)){
			foreach($bits as $key => $value){
				$new_params[$key] = $value;
			}
		}

		return $this->url($method, $new_params);

	}


	/**
	 * Creates a link for sorting a result set
	 * @param string $title
	 * @param string $location
	 * @param string $sort_by
	 * @return string
	 */
	public function sortLink($title, $location, $sort_by){

		$base = $this->xhtmlSelfUrl();
		$sort = app()->prefs->getSort($location, $sort_by);

		// determine the sort direction
		$new_direction = $sort['sort_direction'] == "ASC" ? "DESC" : "ASC";

		// determine the proper class, if any
		$class = 'sortable';
		if($sort['sort_by'] == $sort_by){
			$class = strtolower($new_direction);
		}

		// build proper url
		$url = $base.'?'.http_build_query(array(
									'sort_location'=>$location,
									'sort_by'=>$sort_by,
									'sort_direction'=>$new_direction));

		// create the link
		$html = sprintf('<a href="%s" title="%s" class="%s">%s</a>',
								$url,
								'Sort ' . $this->encodeTextEntities($title) . ' column ' . ($new_direction == 'ASC' ? 'ascending' : 'descending'),
								$class,
								$this->encodeTextEntities($title)
							);

		return $html;

	}


	/**
	 * Generate a basic LI set of pagination links
	 * @param integer $current_page
	 * @param integer $per_page
	 * @param integer $total
	 * @return string
	 */
	public function paginateLinks($current_page, $per_page, $total_pages){

		$url = app()->router->getCurrentUrl(array('/(\?|&)page=[0-9]+/'));
		$url .= '?';

		// build the html list item
		$html = '';
		$html .= sprintf('<li>Page %s of %s</li>', $current_page, $total_pages);

		if($total_pages > 1){

            $link_limit = 10;
            $limit_balance = ceil(($link_limit / 2));

			// previous icon
			if($current_page > 1){
				$html .= sprintf('<li><a href="%spage=%d" class="prev">&laquo;</a></li>', $url, ($current_page-1));
			}


			// if we need to display the other page numbers
			if(app()->config('show_other_page_numbers')){

				// add in the first page
				$selected = $current_page == 1 ? ' class="at"' : '';
				$html .= sprintf('<li%s><a href="%spage=%d">%3$d</a></li>', $selected, $url, 1);


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
					$html .= '<li>...</li>';
				}

				// add in the numeric links
				while($p < $limit){
					$selected = $current_page == $p ? ' class="at"' : '';
					$html .= sprintf('<li%s><a href="%spage=%d">%3$d</a></li>', $selected, $url, $p);
					$p++;
				}

				// add elipse if > 15 pages
				if($total_pages > $link_limit && $current_page < ($total_pages - $limit_balance)){
					$html .= '<li>...</li>';
				}

				// add in the last page
				$p = $total_pages;
				$selected = $current_page == $p ? ' class="at"' : '';
				$html .= sprintf('<li%s><a href="%spage=%d">%3$d</a></li>', $selected, $url, $p);

			} else {

				// Otherwise, just add this page
				$html .= sprintf('<li class="at"><a href="%spage=%d">%2$d</a></li>', $url, $current_page);

			}

			// next icon
			if($current_page < $total_pages){
				$html .= sprintf('<li><a href="%spage=%d" class="next">&raquo;</a></li>', $url, ($current_page+1));
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
		return strtolower(app()->router->module().'_'.app()->router->method());
	}


	/**
	 * Returns a formatted page title for the current page
	 * @return string
	 * @access public
	 */
	public function page_title(){

		$module = app()->router->cleanModule(app()->router->module());
		$method = app()->router->method();

		$this->page_title = str_replace('{lang_title}', $this->text(strtolower($module).':'.$method.':head-title'), $this->page_title);
		$this->page_title = str_replace('{module}', ucwords($module), $this->page_title);
		$this->page_title = str_replace('{method}', ucwords(app()->router->method()), $this->page_title);
		$this->page_title .= '&nbsp;&ndash;&nbsp;'.app()->config('application_name');

		return $this->page_title;

	}


//+-----------------------------------------------------------------------+
//| TEXT-RELATED/HANDLING FUNCTIONS
//+-----------------------------------------------------------------------+

	/**
	 * Truncates a text block and adds a read more link
	 * @param string $phrase
	 * @param integer $blurb_word_length
	 * @param string $more_link
	 * @return string
	 * @access public
	 */
	public function truncateText($phrase, $blurb_word_length = 40, $more_link = false){

		// replace html elements with spaces
    	$phrase = preg_replace("/<(\/?)([^>]+)>/i", " ", $phrase);
    	$phrase = html_entity_decode($phrase, ENT_QUOTES, 'UTF-8');
		$phrase = $this->encodeTextEntities(strip_tags($phrase));
		$phrase_array = explode(' ', $phrase);
		if(count($phrase_array) > $blurb_word_length && $blurb_word_length > 0){
			$phrase = implode(' ',array_slice($phrase_array, 0, $blurb_word_length))
													.'&#8230;'.($more_link ? $more_link : '');
		}

		return $phrase;

	}


	/**
	 * Truncates a string to $char_length caracters, and appends an elipse
	 * @param string $string
	 * @param integer $char_length
	 * @return string
	 * @access public
	 */
	public function truncateString($string, $char_length = 40){

		// replace html elements with spaces
    	$string = preg_replace("/<(\/?)([^>]+)>/i", " ", $string);
    	$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
		$string = $this->encodeTextEntities(strip_tags($string));

		if(strlen($string) > $char_length){
			$string = substr($string, 0, $char_length) . '&#8230;';
		}

		return $string;

	}


	/**
	 * Truncates a filename leaving extension intact
	 * @param string $fileame
	 * @param integer $char_length
	 * @param string $separator
	 * @return string
	 */
	public function truncateFilename($fileame, $char_length = 25, $separator = '&#8230;'){
		$filext = strrchr($fileame, '.');
		return substr(str_replace($filext, '', $fileame), 0, $char_length) . $separator.$filext;
	}


	/**
	 * Returns replacement text if a value is empty (i.e. "N/A")
	 * @param mixed $value
	 * @param string $replace
	 * @return string
	 */
	public function na($value, $replace = 'N/A'){
		$value = trim($value);
		return empty($value) ? $replace : $value;
	}


	/**
	 * Returns a specific filter value from the GET params
	 * @param  string $key The key of the filter value you want
	 * @return mixed
	 * @access public
	 */
	public function filterValue($key = false){

		$filters = array();

		if(app()->params->get->isArray('filter')){
			$filters = app()->params->get->getArray('filter');
		} else {

			$sess_filters = app()->params->session->getArray('filter');

			if(isset($sess_filters[app()->router->module() . ':' . app()->router->method()])){
				$filters = $sess_filters[app()->router->module() . ':' . app()->router->method()];
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
		if($user_id = app()->params->session->getInt('user_id')){
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
			$tbl = app()->model->open($selectTable);
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

			$getArray = app()->model->query($sql);

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
	 * Formats a US address
	 * @access public
	 */
	public function formatAddress($add_1 = '', $add_2 = '', $city = '', $state = '', $zip = '', $country = ''){

		$address = '';

		$address .= empty($add_1) ? '' : $add_1 . "<br />";
		$address .= empty($add_2) ? '' : $add_2 . "<br />";
		$address .= empty($city) ? '' : $city;

		if(!empty($city) && !empty($state)){
			$address .= ", ";
		} else {
			if(!empty($city)){
				$address .= "<br />";
			}
		}

		$address .= empty($state) ? '' : $state . "<br />";
		$address .= empty($zip) ? '' : $zip . "<br />";
		$address .= empty($country) ? '' : $country . "<br />";

		return $this->na($address);

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