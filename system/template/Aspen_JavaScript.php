<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2012 Trellis Development, LLC
 * @since 		2.0
 */

/**
 * Resource object for Javascript files
 *
 * @author botskonet
 */
class Aspen_Javascript extends Aspen_Resource {
	
	/**
	 * @var type 
	 */
	protected $_load_in = 'header';
	
	/**
	 * @var string SCRIPT_ELM Holds the template string for a javascript include
	 * @access private
	 */
	const SCRIPT_ELM = '<script src="%s"></script>';
	
	
	/**
	 * Adds a new css resource
	 * @param string $path 
	 */
	public function __construct( $path ) {
		$this->path = $this->getFullUrl($path);
	}
	
	
	/**
	 * 
	 * @param type $type 
	 */
	public function setLoadIn( $type = 'header' ){
		$this->_load_in = $type;
	}
	
	
	/**
	 *
	 * @return type 
	 */
	public function getLoadIn(){
		return $this->_load_in;
	}
	
	
	/**
	 * Builds the output based on the parameters 
	 */
	public function write(){
		$link = sprintf(self::SCRIPT_ELM, $this->path, $this->media, $this->rel);
		if($this->cdtnl_cmt){
			printf(self::CDTNL_CMT, $this->cdtnl_cmt."\n", $link);
		} else {
			print $link."\n";
		}
	}
}