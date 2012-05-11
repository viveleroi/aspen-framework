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
class Aspen_Css extends Aspen_Resource {
	
	/**
	 * @var string Element type, may be link or style. 
	 */
	protected $elem_type = 'link';
	
	/**
	 * @var string CSS supported media 
	 */
	protected $media = 'all';
	
	/**
	 * @var string rel type, will always be stylesheet for css 
	 */
	protected $rel = 'stylesheet';
	
	/**
	 * @var string Holds the template string for a LINK css include
	 */
	const CSS_ELM_LINK = '<link href="%s" media="%s" rel="%s">';
	
	/**
	 * @var string Holds the template string for a STYLE css include
	 */
	const CSS_ELM_STYLE = '<style media="%2$s">@import url("%1$s");</style>';
	
	
	/**
	 * Sets the element type for write
	 * @param type $type 
	 */
	public function setElementType( $type ){
		if($type == 'link' || $type == 'style'){
			$this->elem_type = $type;
		}
	}
	
	
	/**
	 * Builds the output based on the parameters 
	 */
	public function __toString(){
		$temp = ($this->elem_type == "link" ? self::CSS_ELM_LINK : self::CSS_ELM_STYLE);
		$link = sprintf($temp, $this->path, $this->media, $this->rel);
		if($this->cdtnl_cmt){
			return sprintf(self::CDTNL_CMT, $this->cdtnl_cmt."\n", $link);
		} else {
			return $link."\n";
		}
	}
}