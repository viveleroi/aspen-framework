<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class DataDisplay  {

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
		if(is_array($data)){
			foreach($data as $key => $val){
				if(is_array($val)){
					// Ensure this is not an array of objects
					$tmp = $val;
					$first = array_shift($tmp);
					if(!is_object($first)){
						$classname = get_class($this);
						$data[$key] = new $classname($val);
					}
				}
			}
			$this->data = $data;
		} else {
			error()->raise(1, text('dberror:datadisplay_init_error', $name), __FILE__, __LINE__);
		}
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
				if(is_object($this->data[$name]) && ($this->data[$name] instanceof DataDisplay)){
					return $this->data[$name]->data;
				} else {
					return $this->data[$name];
				}
			}
		} else {
			if(!method_exists($this, $name)){
				error()->raise(1, text('dberror:datadisplay_call_error', $name), __FILE__, __LINE__);
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
	static public function formatPhoneNum($phone){
		$phone = preg_replace("/[^0-9]*/",'',$phone);
		if(strlen($phone) != 10) return(false);
		$sArea = substr($phone,0,3);
		$sPrefix = substr($phone,3,3);
		$sNumber = substr($phone,6,4);
		$phone = "(".$sArea.") ".$sPrefix."-".$sNumber;
		return($phone);
	}
	
	
	/**
	 * Formats a US address
	 * @access public
	 */
	static public function formatAddress($add_1 = '', $add_2 = '', $city = '', $state = '', $zip = '', $country = ''){

		$address = '';

		$address .= empty($add_1) ? '' : $add_1 . "<br />";
		$address .= empty($add_2) ? '' : $add_2 . "<br />";
		$address .= empty($city) ? '' : $city;

		if(!empty($city) && !empty($state)){
			$address .= ", ";
		} else {
			if(!empty($city)){
				$address .= "<br />";
			}
		}

		$address .= empty($state) ? '' : $state . "<br />";
		$address .= empty($zip) ? '' : $zip . "<br />";
		$address .= empty($country) ? '' : $country . "<br />";

		return DataDisplay::na($address);

	}
	
	
//+-----------------------------------------------------------------------+
//| TEXT-RELATED/HANDLING FUNCTIONS
//+-----------------------------------------------------------------------+
	
	
	/**
	 * Truncates a text block and adds a read more link
	 * @param string $phrase
	 * @param integer $blurb_word_length
	 * @param string $more_link
	 * @return string
	 * @access public
	 */
	static public function truncateText($phrase, $blurb_word_length = 40, $more_link = false){

		// replace html elements with spaces
    	$phrase = preg_replace("/<(\/?)([^>]+)>/i", " ", $phrase);
    	$phrase = html_entity_decode($phrase, ENT_QUOTES, 'UTF-8');
		$phrase = strip_tags($phrase);
		$phrase_array = explode(' ', $phrase);
		if(count($phrase_array) > $blurb_word_length && $blurb_word_length > 0){
			$phrase = implode(' ',array_slice($phrase_array, 0, $blurb_word_length))
													.'&#8230;'.($more_link ? $more_link : '');
		}

		return $phrase;

	}
	
	
	/**
	 * Truncates a string to $char_length caracters, and appends an elipse
	 * @param string $string
	 * @param integer $char_length
	 * @return string
	 * @access public
	 */
	static public function truncateString($string, $char_length = 40){

		// replace html elements with spaces
    	$string = preg_replace("/<(\/?)([^>]+)>/i", " ", $string);
    	$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
		$string = strip_tags($string);

		if(strlen($string) > $char_length){
			$string = substr($string, 0, $char_length) . '&#8230;';
		}

		return $string;

	}
	
	
	/**
	 * Truncates a filename leaving extension intact
	 * @param string $fileame
	 * @param integer $char_length
	 * @param string $separator
	 * @return string
	 */
	static public function truncateFilename($filename, $char_length = 25, $separator = '&#8230;'){
		$filext = pathinfo($filename, PATHINFO_EXTENSION);
		if(strlen($filename) > ($char_length)){
			return substr(str_replace($filext, '', $filename), 0, $char_length) . $separator.$filext;
		}
		return $filename;
	}
}
?>