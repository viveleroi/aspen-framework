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
	
	
//	/**
//	 * Static caller for Aspen_Css
//	 * @param string $path
//	 * @return \Aspen_Css 
//	 */
//	public static function Css( $path ){
//		return new Aspen_Css($path);
//	}
	
	
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
	protected function ensureFullUri( $path ){
		
		if(strpos($path, "http") !== false){
			return router()->staticUrl();
		} else {
			return $path;
		}
	}
}