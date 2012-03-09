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
	 * @var string Url to resource 
	 */
	protected $path;
	
	/**
	 * @var string CDTNL_CMT Holds the template string for a conditional comment
	 * @access private
	 */
	const CDTNL_CMT = '<!--[%s]>%s<![endif]-->';
	
	
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
		
		if(strpos($this->path, "http") !== false){
			return router()->staticUrl() . $this->path;
		} else {
			return $this->path;
		}
	}
	
	
	/**
	 * 
	 */
	public function write(){
		// placeholder
	}
}