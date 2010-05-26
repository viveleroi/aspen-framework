<?php
/**
 * @package  Peregrine
 * @author   Michael Botsko, Trellis Development, LLC
 * @license  Mozilla Public License, 1.1
 * @version  1.0-rc1-5-g223fe1d
 *
 * Peregrine is a class that aims to improve PHP superglobal security
 * by transferring the raw incoming values to private member variables.
 * You may then access the data using a wide array of higher security
 * filtering functions.
 *
 * This class was partially inspired by Inspekt by Ed Finkler and Chris Shifflet,
 * two folks who have contributed greatly to the php community. In order to meet
 * some custom needs for both single projects and for the Aspen Framework, this
 * class was created to make structural and use improvements.
 */

/**
 * @package Peregrine
 */
class CageBase {

	/**
	 * @var array Holds the source array for the raw incoming data.
	 * @access private
	 */
	private $_raw;

	/**
	 * @var array Holds any arguments needed by the __call method
	 * @access private
	 */
	private $_args;


	/**
	 * Loads the incoming source array to the raw private variable.
	 *
	 * @param array $arr
	 * @access public
	 */
	public final function __construct($arr){
		if(is_array($arr)){
			$this->_raw = $arr;
		}
	}


	/**
	 * Determines whether or not a key exists
	 * @param string $key
	 * @return boolean
	 */
	public function keyExists($key){
		return array_key_exists($key, $this->_raw);
	}


	/**
	 * Determines whether or not a specific key exists
	 * in the raw array. If it does it returns its value,
	 * otherwise it returns false.
	 *
	 * @param string $key
	 * @return mixed
	 * @access public
	 */
	private function getKey($key){
		if($this->keyExists($key)){
			return $this->_raw[$key];
		}
		return false;
	}


	/**
	 * Handles incoming method calls for undefined is___
	 * functions. If not method defined it uses the
	 * get___ equiv in it's place for matching.
	 *
	 * @param string $method
	 * @param array $args
	 * @return boolean
	 */
	public function __call($method, $args){

		$this->_args = $args;

		// $key should be first arg
		$key = $this->getArg(0);

		if($key !== false && strpos($method, 'is') !== false){
			$real_method = str_replace('is', 'get', $method);
			if(method_exists($this, $real_method)){
				$res = call_user_func_array(array($this, $real_method), $this->_args);
				// Skip the test if the result is false (don't want to confuse bools)
				return $this->getRaw($key) === false ? false : ($res === $this->getRaw($key));
			}
		}

		if($key !== false && strpos($method, 'get') !== false){
			$real_method = str_replace('get', 'is', $method);
			if(method_exists($this, $real_method)){
				if(call_user_func_array(array($this, $real_method), $this->_args)){
					return $this->getRaw($key);
				}
				return false;
			}
		}

		return NULL;
	}


	/**
	 * Returns an argument position for items passed to __call
	 *
	 * @param integer $pos
	 * @return mixed
	 */
	private function getArg($pos = 0){
		if(array_key_exists($pos, $this->_args)){
			return $this->_args[$pos];
		}
		return NULL;
	}


	/**
	 * WARNING: AVOID USING THIS!
	 * Returns the raw, unfiltered value from the $key. Use this only
	 * if the existing filters don't apply. Please recommend new
	 * filters or submit patches through github.
	 *
	 * @param string $key
	 * @return mixed
	 * @access public
	 */
	public function getRaw($key = false){
		return $this->getKey($key);
	}


	/**
	 * WARNING: AVOID USING THIS!
	 * Returns the origin superglobal array. It is not recommended
	 * you use this, as it does not allow you to use the class
	 * filtering functions.
	 *
	 * @return array
	 */
	public function getRawSource(){
		return $this->_raw;
	}


	/***********************************************************
	 * CUSTOM DATA CHECK METHODS
	 ***********************************************************/


	/**
	 * Determines whether or not a value is empty. You may provide
	 * an array of additional characters that should be counted as
	 * empty values.
	 *
	 * Empty returns true if the key does not exist at all.
	 *
	 * For example empty dates may contain 0,:,-,/
	 *
	 * @param string $key
	 * @param mixed $count_as_empty
	 * @return boolean
	 */
	public function isEmpty($key, $count_as_empty = false){
		if (!$this->keyExists($key)) {
			return true;
		} else {
			$val = $this->getRaw($key);
			$val = $count_as_empty ? str_replace($count_as_empty, '', $val) : $val;
			return empty($val);
		}
	}


	/**
	 * Only runs an empty check if the key has been set.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function isSetAndEmpty($key){
		if ($this->keyExists($key)) {
			return $this->isEmpty($key);
		}
		return false;
	}


	/**
	 * Only runs an nonempty check if the key has been set.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function isSetAndNotEmpty($key){
		if ($this->keyExists($key)) {
			return !$this->isEmpty($key);
		}
		return false;
	}



	/**
	 * Compare two values
	 *
	 * @param string $key_1
	 * @param string $key_2
	 * @return boolean
	 */
	public function match($key_1, $key_2, $strict = false){
		if ($this->keyExists($key_1) && $this->keyExists($key_2)) {
			if($strict){
				return $this->getRaw($key_1) === $this->getRaw($key_2);
			} else {
				return $this->getRaw($key_1) == $this->getRaw($key_2);
			}
		}
		return false;
	}


	/**
	 * Determines whether or not a value is between a set
	 * of two other values.
	 *
	 * A fourth argument $inc will allow an exact value match
	 * to return as true.
	 *
	 * @param string $key
	 * @param int $min
	 * @param int $max
	 * @param bool $inc
	 * @return boolean
	 */
	public function isBetween($key, $min, $max, $inc = NULL){
		$inc = $inc === NULL ? true : $inc;
		$val = $this->getFloat($key);
		if ($val > $min && $val < $max) {
			return true;
		}
		if ($inc && $val >= $min && $val <= $max) {
			return true;
		}
		return false;
	}


	/**
	 * Determines whether the value is greater than a number. Both integers
	 * and float/dobules accepted.
	 *
	 * @param string $key
	 * @param int $min
	 * @return boolean
	 */
	public function isGreaterThan($key, $min){
		$val = $this->getFloat($key);
		return ($val > $min);
	}


	/**
	 * Determines whether the value is less than a number. Both integers
	 * and float/dobules accepted.
	 *
	 * @param string $key
	 * @param int $min
	 * @return boolean
	 */
	public function isLessThan($key, $max){
		$val = $this->getFloat($key);
		return ($val < $max);
	}


	/**
	 * Determines if a string is a valid email address.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function isEmail($key){
		$val = $this->getRaw($key);
		return (bool) preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', $val);
	}


	/**
	 * Determines whether or not a string is a valid IP address.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function isIP($key){
		$val = $this->getRaw($key);
		return (bool) ip2long($val);
	}


	/**
	 * Returns if the value is in a provided array.
	 *
	 * @param string $key
	 * @param array $allowed
	 * @return boolean
	 */
	public function isInArray($key, $allowed = NULL){
		// @todo: make this work recursively
		$val = $this->getRaw($key);
		if(is_array($allowed)){
			return in_array($val, $allowed);
		}
		return NULL;
	}


	/**
	 * Determines if the value is a valid phone number. Currently, only US
	 * phone numbers are supported.
	 *
	 * @param string $key
	 * @param string $country
	 * @return boolean
	 */
	public function isPhone($key, $country = NULL){

		$country = $country === NULL ? 'US' : $country;

		$val = $this->getDigits($key);

		switch ($country){
			case 'US':
				if (strlen($val) != 10) {
					return false;
				}

				$areaCodes = array(
					201, 202, 203, 204, 205, 206, 207, 208,
					209, 210, 212, 213, 214, 215, 216, 217,
					218, 219, 224, 225, 226, 228, 229, 231,
					234, 239, 240, 242, 246, 248, 250, 251,
					252, 253, 254, 256, 260, 262, 264, 267,
					268, 269, 270, 276, 281, 284, 289, 301,
					302, 303, 304, 305, 306, 307, 308, 309,
					310, 312, 313, 314, 315, 316, 317, 318,
					319, 320, 321, 323, 325, 330, 334, 336,
					337, 339, 340, 345, 347, 351, 352, 360,
					361, 386, 401, 402, 403, 404, 405, 406,
					407, 408, 409, 410, 412, 413, 414, 415,
					416, 417, 418, 419, 423, 424, 425, 430,
					432, 434, 435, 438, 440, 441, 443, 445,
					450, 469, 470, 473, 475, 478, 479, 480,
					484, 501, 502, 503, 504, 505, 506, 507,
					508, 509, 510, 512, 513, 514, 515, 516,
					517, 518, 519, 520, 530, 540, 541, 555,
					559, 561, 562, 563, 564, 567, 570, 571,
					573, 574, 580, 585, 586, 600, 601, 602,
					603, 604, 605, 606, 607, 608, 609, 610,
					612, 613, 614, 615, 616, 617, 618, 619,
					620, 623, 626, 630, 631, 636, 641, 646,
					647, 649, 650, 651, 660, 661, 662, 664,
					670, 671, 678, 682, 684, 700, 701, 702,
					703, 704, 705, 706, 707, 708, 709, 710,
					712, 713, 714, 715, 716, 717, 718, 719,
					720, 724, 727, 731, 732, 734, 740, 754,
					757, 758, 760, 763, 765, 767, 769, 770,
					772, 773, 774, 775, 778, 780, 781, 784,
					785, 786, 787, 800, 801, 802, 803, 804,
					805, 806, 807, 808, 809, 810, 812, 813,
					814, 815, 816, 817, 818, 819, 822, 828,
					829, 830, 831, 832, 833, 835, 843, 844,
					845, 847, 848, 850, 855, 856, 857, 858,
					859, 860, 863, 864, 865, 866, 867, 868,
					869, 870, 876, 877, 878, 888, 900, 901,
					902, 903, 904, 905, 906, 907, 908, 909,
					910, 912, 913, 914, 915, 916, 917, 918,
					919, 920, 925, 928, 931, 936, 937, 939,
					940, 941, 947, 949, 951, 952, 954, 956,
					959, 970, 971, 972, 973, 978, 979, 980,
					985, 989);

				return in_array( substr($val, 0, 3), $areaCodes);
				break;
			default:
				return false;
				break;
		}
		return NULL;
	}


	// @todo this need improving
	/**
	 *
	 * @param string $key
	 * @param <type> $type
	 * @return <type>
	 */
	public function isCreditCard($key, $type = NULL){

		$value = $this->getDigits($key);
		$length = strlen($value);

		if ($length < 13 || $length > 19) {
			return false;
		}

		$sum = 0;
		$weight = 2;

		for ($i = $length - 2; $i >= 0; $i--) {
			$digit = $weight * $value[$i];
			$sum += floor($digit / 10) + $digit % 10;
			$weight = $weight % 2 + 1;
		}

		$mod = (10 - $sum % 10) % 10;

		return ($mod == $value[$length - 1]);
	}


	/**
	 * Determines if the string is a valid web address.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function isUri($key){

		$regex = '&';
		$regex .= '^(ftp|http|https):';					// protocol
		$regex .= '(//)';								// authority-start
		$regex .= '([-a-z0-9/~;:@=+$,.!*()\']+@)?';		// userinfo
		$regex .= '(';
		$regex .= '((?:[^\W_]((?:[^\W_]|-){0,61}[^\W_])?\.)+[a-zA-Z]{2,6}\.?)';		// domain name
		$regex .= '|';
		$regex .= '([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?(\.[0-9]{1,3})?)';	// OR ipv4
		$regex .= ')';
		$regex .= '(:([0-9]*))?';						// port
		$regex .= '(/((%[0-9a-f]{2}|[-a-z0-9/~;:@=+$,.!*_()\'\&]*)*)/?)?';	// path
		$regex .= '(\?[^#]*)?';							// query
		$regex .= '(#([-a-z0-9_]*))?';					// anchor (fragment)
		$regex .= '$&i';

		$res = preg_match($regex, $this->getRaw($key), $matches);
		return (bool) $res;

	}


	/**
	 * Determines if the string is a valid domain name only, no http or port allowed
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function isTopLevelDomain($key){

		$regex = '&^';
		$regex .= '([-a-z0-9/~;:@=+$,.!*()\']+@)?';		// userinfo
		$regex .= '(';
		$regex .= '((?:[^\W_]((?:[^\W_]|-){0,61}[^\W_])?\.)+[a-zA-Z]{2,6}\.?)';		// domain name
		$regex .= '|';
		$regex .= '([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?(\.[0-9]{1,3})?)';	// OR ipv4
		$regex .= ')';
		$regex .= '$&i';

		$res = preg_match($regex, $this->getRaw($key), $matches);
		return (bool) $res;

	}


	/***********************************************************
	 * SANITIZING RETURN METHODS
	 ***********************************************************/


	/**
	 * Returns a string of only alphabetical characters.
	 *
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function getAlpha($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			return preg_replace('/[^[:alpha:]]/', '', $this->getKey($key));
		}
		return $default;
	}


	/**
	 * Returns a string of alphanumeric characters.
	 *
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function getAlnum($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			return preg_replace('/[^[:alnum:]]/', '', $this->getKey($key));
		}
		return $default;
	}


	/**
	 * Returns a string of alphanumeric characters, allowing spaces, apostrophes, periods.
	 *
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function getName($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			return preg_replace('/[^a-zA-Z-[:space:]\.\']/', '', $this->getKey($key));
		}
		return $default;
	}


	/**
	 * Returns an acceptable clean url / HTML element id string
	 *
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function getElemId($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			$val = str_replace(array(' '), '_', strtolower($this->getKey($key)));
			return preg_replace('/[^a-zA-Z0-9-_\.]/', '', $val);
		}
		return $default;
	}


	/**
	 * Returns an integer.
	 *
	 * @param string $key
	 * @param string $default
	 * @return integer
	 */
	public function getInt($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->keyExists($key)){
			return (int) $this->getKey($key);
		}
		return $default;
	}


	/**
	 * Returns only numeric characters. The return type is set as the same
	 * type as the incoming value. If you provide a float, you get a float back.
	 * If you provide a string, you get a string back.
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public function getDigits($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->keyExists($key)){
			// We need to mimic the type back to the user that they gave us
			$type = gettype($this->getKey($key));
			$clean = preg_replace('/[^\d]/', '', $this->getKey($key));
			settype($clean, $type);
			return $clean;
		}
		return $default;
	}


	/**
	 * Returns a floating-point decimal. The return type is set as the same
	 * type as the incoming value. If you provide a float, you get a float back.
	 * If you provide a string, you get a string back.
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public function getFloat($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->keyExists($key)){
			// We need to mimic the type back to the user that they gave us
			$type = gettype($this->getKey($key));
			$clean = preg_replace('/[^\d\.]/', '', $this->getKey($key));
			settype($clean, $type);
			return $clean;
		}
		return $default;
	}


	/**
	 * Returns a valid US Currency string or float. Accepts decimal point,
	 * comma, and US dollar sign.
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public function getCurrency($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->keyExists($key)){
			// We need to mimic the type back to the user that they gave us
			$type = gettype($this->getKey($key));
			$clean = preg_replace('/[^\d\.,\$]/', '', $this->getKey($key));
			settype($clean, $type);
			return $clean;
		}
		return $default;
	}


	/**
	 * Checks for a valid date.
	 * @param string $key
	 * @param string $format
	 * @param mixed $default
	 * @return <type>
	 */
	public function getDate($key = false, $format = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			$format = $format ? $format : DATE_RFC822;
			if($time = strtotime($this->getRaw($key))){
				return date($format, $time);
			}
		}
		return $default;
	}


	/**
	 * Returns a US postal code. In the form of either five digits, or nine
	 * with a hyphen.
	 *
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function getZip($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			preg_match('/(^\d{5}$)|(^\d{5}-\d{4}$)/', $this->getDigits($key), $matches);
			if(is_array($matches)){
				return $matches[0];
			}
		}
		return $default;
	}


	/**
	 * Returns characters generally allowed within a file system path
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public function getPath($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			return preg_replace('/[^a-zA-Z0-9_:~\.\/-]/', '', $this->getKey($key));
		}
		return $default;
	}


	/**
	 * Returns characters generally allowed within a query string
	 * Note: the string may also be validates a full URI/URL if you use
	 * the getUri method, however this is more specific for query strings
	 * without the rest of the url.
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public function getQueryString($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			return preg_replace('/[^a-zA-Z0-9_:~\.\/-?{}\[\]]/', '', $this->getKey($key));
		}
		return $default;
	}


	/**
	 * Returns characters which are allowed in the apache SERVER_NAME variable
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public function getServerName($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			if($this->isIP($key)){
				return $this->getKey($key);
			}
			elseif($this->isTopLevelDomain($key)){
				return $this->getKey($key);
			}
			// for stuff like "localhost"
			elseif($this->isAlnum($key)){
				return $this->getKey($key);
			}
		}
		return $default;
	}


	/**
	 * Returns characters generally allowed within a file system path
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	public function getArray($key = false, $default = NULL){
		$default = $default === NULL ? false : $default;
		if($this->isSetAndNotEmpty($key)){
			return is_array($this->getKey($key)) ? $this->getKey($key) : $default;
		}
		return $default;
	}
}


/**
 *
 */
class Peregrine {

	/**
	 * @var object
	 */
	public $post;

	/**
	 * @var object
	 */
	public $get;

	/**
	 * @var object
	 */
	public $session;

	/**
	 * @var object
	 */
	public $env;

	/**
	 * @var object
	 */
	public $files;

	/**
	 * @var object
	 */
	public $cookie;

	/**
	 * @var object
	 */
	public $server;


	/**
	 * Initializes all of the superglobal cages. Destroys the origin arrays.
	 */
	public function init(){
		$this->get_cage();
		$this->post_cage();
		$this->session_cage();
		$this->cookie_cage();
		$this->server_cage();
		$this->env_cage();
		$this->files_cage();
	}


	/**
	 * Adds a cage around any array passed to it. It destroys the original array.
	 *
	 * @param array $var
	 * @return object
	 */
	static public function sanitize( &$var ){
		$tmp = new CageBase($var);
		$var = null;
		return $tmp;
	}


	/**
	 * Returns the origin superglobal array. It is not recommended
	 * you use this, as it does not allow you to use the class
	 * filtering functions.
	 *
	 * @param string $var
	 * @return array
	 */
	public function getRawSource($var){
		if(in_array($var, array('post','get','session','cookie','server','env','files'))){
			return $this->{$var}->getRawSource();
		}
	}


	/**
	 * Cages for the $_GET superglobal.
	 */
	private function get_cage(){
		$tmp = $this->sanitize($_GET);
		$GLOBALS['HTTP_GET_VARS'] = NULL;
		$this->get = $tmp;
	}


	/**
	 * Cages for the $_POST superglobal.
	 */
	private function post_cage(){
		$tmp = $this->sanitize($_POST);
		$GLOBALS['HTTP_POST_VARS'] = NULL;
		$this->post = $tmp;
	}


	 /**
	 * Cages for the $_SESSION superglobal.
	  * @todo need to refresh the cage in case someone has appended new array elements
	  * How do we do this if we've destroyed the original $_SESSION?
	 */
	private function session_cage(){

		// possible way to regenerate session:
		// session_write_close / session_start();

//		$tmp = $this->sanitize($_SESSION);
		// don't call sanitize because it nulls out SESSION
		$tmp = new CageBase($_SESSION);
//		$GLOBALS['HTTP_SESSION_VARS'] = NULL;
		$this->session = $tmp;
	}

		/**
		 * Refreshes the session cage after writing to it
		 * @todo this is only temporary until we resolve the session
		 * refreshing issue noted above.
		 * @param <type> $type
		 */
		public function refreshCage($type){
			switch($type){
				case 'session':
					$this->session_cage();
					break;
			}
		}


	/**
	 * Cages for the $_COOKIE superglobal.
	 */
	private function cookie_cage(){
		$tmp = $this->sanitize($_COOKIE);
		$GLOBALS['HTTP_COOKIE_VARS'] = NULL;
		$this->cookie = $tmp;
	}


	/**
	 * Cages for the $_SERVER superglobal.
	 */
	private function server_cage(){
		$tmp = $this->sanitize($_SERVER);
		$GLOBALS['HTTP_SERVER_VARS'] = NULL;
		$this->server = $tmp;
	}


	/**
	 * Cages for the $_ENV superglobal.
	 */
	private function env_cage(){
		$tmp = $this->sanitize($_ENV);
		$GLOBALS['HTTP_ENV_VARS'] = NULL;
		$this->env = $tmp;
	}


	/**
	 * Cages for the $_FILES superglobal.
	 */
	private function files_cage(){
		$tmp = $this->sanitize($_FILES);
		$GLOBALS['HTTP_POST_FILES'] = NULL;
		$this->files = $tmp;
	}
}
?>