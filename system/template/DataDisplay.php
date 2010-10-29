<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class DataDisplay extends Library {

	/**
	 * @var array Will hold an array that we use for data access
	 */
	protected $data;

	/**
	 * @var array Holds all fields which, if empty, return 'Not Provided' text
	 */
	protected $not_provided = array();


	/**
	 * Run parent constructor
	 * @param <type> $id
	 */
	public function  __construct($data = false) {
		parent::__construct();
		$this->data = $data;
	}


	/**
	 * Dynamic call handling for data that doesn't need special formatting
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function  __call($name, $arguments) {
		if(array_key_exists($name, $this->data)){
			if(empty($this->data[$name]) && in_array($name, $this->not_provided)){
				return $this->na();
			} else {
				return $this->data[$name];
			}
		} else {
			if(!method_exists($this, $name)){
				app()->error->raise(1, text('dberror:datadisplay_call_error', $name), __FILE__, __LINE__);
			}
		}
	}


	/*******************************
	 * FORMATTING FUNCTIONS
	 */

	/**
	 * Returns not provided if something is empty
	 * @return <type>
	 */
	public function na(){
		return text('db:display:not_provided');
	}


	/**
	 * Formats a phone number
	 * @param string $phone
	 */
	public function formatPhoneNum($phone){
		$phone = preg_replace("/[^0-9]*/",'',$phone);
		if(strlen($phone) != 10) return(false);
		$sArea = substr($phone,0,3);
		$sPrefix = substr($phone,3,3);
		$sNumber = substr($phone,6,4);
		$phone = "(".$sArea.") ".$sPrefix."-".$sNumber;
		return($phone);
	}
}
?>