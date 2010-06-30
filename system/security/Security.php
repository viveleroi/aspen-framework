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
class Security extends Library {


	/**
	 * Handles escaping data for entry into the database.
	 * @param mixed $data
	 * @param boolean $allow_html Whether or not to allow html
	 * @return mixed
	 * @access public
	 */
	public function dbescape($data, $allow_html = false) {

		// first, remove all slashes for consistency
		$data = $this->clean_slashes($data);

		// then run our cleaning function for database
		return $this->clean_db_input($data, $allow_html);

	}


	/**
	 * Strips slashes from string or array
	 * @param mixed $data
	 * @return mixed
	 * @access public
	 */
	public function clean_slashes($data) {

		// if it's an array, loop it
		if (is_array($data)) {

			$newArr = array();

	    	foreach( $data as $key => $value ){
	    		$newArr[ $key ] = $this->clean_slashes($value);
	    	}

	    	return $newArr;

		} else {
			return stripslashes($data);
		}
	}


	/**
	 * Adds escaping for database input
	 * @param mixed $var
	 * @param boolean $allow_html
	 * @return mixed
	 * @access private
	 */
	private function clean_db_input($var = false, $allow_html = false){

	  	// escape
		if (is_array($var)) {

			$newArr = array();

	    	foreach( $var as $key => $value ){
	    		if(is_array($value)){
	    			$newArr[ $key ] = $this->clean_db_input($value, $allow_html);
	    		} else {
	    			$newArr[ $key ] = $allow_html ? $this->cleanHtml($value) : strip_tags($value);
                    $newArr[ $key ] = mysql_real_escape_string($newArr[ $key ]);
	    		}
	    	}

	    	$var = $newArr;

		} else {
			$var = $allow_html ? $this->cleanHtml($var) : strip_tags($var);
			$var = mysql_real_escape_string($var);
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