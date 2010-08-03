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
			return $this->data[$name];
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
	 * Formats a phone number
	 * @param string $phone
	 */
	public function formatPhoneNum($phone){
		$phone = preg_replace("[^0-9]",'',$phone);
		if(strlen($phone) != 10) return(false);
		$sArea = substr($phone,0,3);
		$sPrefix = substr($phone,3,3);
		$sNumber = substr($phone,6,4);
		$phone = "(".$sArea.") ".$sPrefix."-".$sNumber;
		return($phone);
	}
}
?>