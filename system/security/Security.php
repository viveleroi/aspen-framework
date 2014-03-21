<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Provides data cleaning and escaping functions.
 * @package Aspen_Framework
 */
class Security  {


	/**
	 * Adds escaping for database input
	 * @param mixed $var
	 * @param boolean $allow_html
	 * @return mixed
	 * @access private
	 */
	public function dbescape($var = false, $allow_html = false){
		if (is_array($var)) {
			$newArr = array();
	    	foreach($var as $key => $value){
	    		if(is_array($value)){
	    			$newArr[$key] = $this->clean_db_input($value, $allow_html);
	    		} else {
	    			$newArr[$key] = $allow_html ? $this->cleanHtml($value) : strip_tags($value);
                    $newArr[$key] = mysqli_real_escape_string(app()->db, $newArr[ $key ]);
	    		}
	    	}
	    	$var = $newArr;
		} else {
			$var = $allow_html ? $this->cleanHtml($var) : strip_tags($var);
			$var = mysqli_real_escape_string(app()->db, $var);
		}
	    return $var;
	}
	
	
	/**
	 * Generates a form token
	 * @access public
	 */
	public function generateFormToken(){
		$token = sha1(time()+rand(0, 1000));
		$_SESSION['form_token'] = $token;
		return $token;
	}
	
	
	/**
	 * Cleans the html if filter class available
	 * @param string $value
	 * @return string
	 * @access private
	 */
	private function cleanHtml($value){
		if(app()->isLibraryLoaded('HTMLPurifier')){
			return app()->html->purify($value);
		} else {
			return $value;
		}
	}
}
?>