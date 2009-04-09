<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @version    	$Revision: 461 $
 * @since 		1.0
 * @revision 	$Id: bootstrap.php 461 2009-04-02 04:56:02Z mbotsko $
 */

/**
 * @abstract JSON format functions
 * @package Aspen_Framework
 */
class Json {


	/**
	 * @abstract Encodes an array as a json formatted data
	 * @param array $data
	 * @return string
	 * @access public
	 */
	public function php_json_encode($data) {
		if (is_array($data)) {
			if ($this->array_is_associative($data)) {
				$arr_out = array();
				foreach ($data as $key=>$val) {
					$arr_out[] = '"' . $key . '":' . $this->php_json_encode($val);
				}
				return '{' . implode(',', $arr_out) . '}';
			} else {
				$arr_out = array();
				$ct = count($data);
				for ($j = 0; $j < $ct; $j++) {
					$arr_out[] = $this->php_json_encode($data[$j]);
				}
				return '[' . implode(',', $arr_out) . ']';
			}
		} else {
			if (is_int($data)) {
				return $data;
			} else {
				$str_out = stripslashes(trim($data));
				$str_out = str_replace(array('"', '', '/'), array('\"', '\\', '/'), $str_out);
				return '"' . $str_out . '"';
			}
		}
	}
	
	
	/**
	 * @abstract Returns whether or not array has associative keys
	 * @param array $array
	 * @return boolean
	 * @access private
	 */
	private function array_is_associative($array) {
		$count = count($array);
		for ($i = 0; $i < $count; $i++) {
			if (!array_key_exists($i, $array)) {
				return true;
			}
		}
		return false;
	}
}
?>