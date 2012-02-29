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
	 * @var string SCRIPT_ELM Holds the template string for a javascript include
	 * @access private
	 */
	const SCRIPT_ELM = '<script src="%s"></script>';
	
	
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
	
	
}