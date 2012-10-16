<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2012 Trellis Development, LLC
 * @since 		2.0
 */

/**
 * Parent object for all static resources
 *
 * @author botskonet
 */
class Aspen_Resource {
	
	/**
	 * 
	 * @var type 
	 */
	protected $cdtnl_cmt;
	
	/**
	 * 
	 * @var type 
	 */
	protected $opts = array(
		'cache-bust' => true
	);
	
	/**
	 * @var string Url to resource 
	 */
	protected $path;
	
	/**
	 * @var string CDTNL_CMT Holds the template string for a conditional comment
	 * @access private
	 */
	const CDTNL_CMT = '<!--[%s]>%s<![endif]-->';
	
	
	/**
	 * Adds a new css resource
	 * @param string $path 
	 */
	public function __construct( $path, $opts = false ) {
		$this->mergeOpts($opts);
		$this->path = $path;
		$this->path = $this->getFullUrl();
	}
	
	
	/**
	 * Merges incoming options with defaults
	 * @param type $opts 
	 */
	protected function mergeOpts($opts){
		if(is_array($opts) && !empty($opts)){
			$this->opts = array_merge($this->opts, $opts);
		}
	}
	
	
	/**
	 * Set the conditional comment
	 * @param string $cond 
	 */
	public function setConditionalComment( $cond ){
		$this->cdtnl_cmt = $cond;
	}
	
	
	/**
	 * Converts a potential relative to absolute, unless an external resource
	 * @param string $path
	 * @return string 
	 */
	protected function getFullUrl(){
		
		if($this->opts['cache-bust']){
			$cb = config()->get('application_build');
			if(config()->get('enable_cache_busting') && !empty($cb) && $cb != "Git-Version"){
				$this->path .= '?v='.$cb;  
			}
		}
		
		if(strpos($this->path, "http") === false){
			return router()->staticUrl() . $this->path;
		} else {
			return $this->path;
		}
	}
	
	
	/**
	 * You need to override this with your own
	 * resource methods.
	 */
	public function __toString(){
		return "";
	}
}