<?php
/**
 * Inspekt Supercage
 *
 * @author Ed Finkler <coj@funkatron.com>
 *
 * @package Inspekt
 */

/**
 * require main Inspekt class
 */
require_once 'Inspekt.php';

/**
 * require the Cage class
 */
require_once 'Cage.php';

/**
 * The Supercage object wraps ALL of the superglobals
 *
 * @package Inspekt
 *
 */
Class Inspekt_Supercage {

	/**
	 * The get cage
	 *
	 * @var Inspekt_Cage
	 */
	var $get;

	/**
	 * The post cage
	 *
	 * @var Inspekt_Cage
	 */
	var $post;

	/**
	 * The cookie cage
	 *
	 * @var Inspekt_Cage
	 */
	var $cookie;

	/**
	 * The env cage
	 *
	 * @var Inspekt_Cage
	 */
	var $env;

	/**
	 * The files cage
	 *
	 * @var Inspekt_Cage
	 */
	var $files;

	/**
	 * The session cage
	 *
	 * @var Inspekt_Cage
	 */
	var $session;

	var $server;

	/**
	 * Enter description here...
	 *
	 * @return Inspekt_Supercage
	 */
	function Inspekt_Supercage() {
		// placeholder
	}
	
	
	function getRawSource($type = 'get'){
		return $this->{$type}->_source;
	}

	/**
	 * Enter description here...
	 *
	 * @param string  $config_file
	 * @param boolean $strict
	 * @return Inspekt_Supercage
	 */
	 static function Factory($config_file = NULL, $strict = TRUE) {

		$sc	= new Inspekt_Supercage();
		$sc->_makeCages($strict, $config_file);

		// eliminate the $_REQUEST superglobal
		if ($strict) {
			$_REQUEST = null;
		}

		return $sc;

	}

	/**
	 * Enter description here...
	 *
	 * @see Inspekt_Supercage::Factory()
	 * @param string  $config_file
	 * @param boolean $strict
	 */
	function _makeCages($config_file=NULL, $strict=TRUE) {
		$this->get		= Inspekt::makeGetCage($config_file, $strict);
		$this->post		= Inspekt::makePostCage($config_file, $strict);
		$this->cookie	= Inspekt::makeCookieCage($config_file, $strict);
		$this->env		= Inspekt::makeEnvCage($config_file, $strict);
		$this->files	= Inspekt::makeFilesCage($config_file, $strict);
		$this->session	= Inspekt::makeSessionCage($config_file, $strict);
		$this->server	= Inspekt::makeServerCage($config_file, $strict);
	}
	
	
	/**
     * @abstract Refreshes a cage - avoid using in production
     */
	public function refreshCage($type = false, $config_file=NULL, $strict=TRUE){

		switch($type){
			case 'get':
				$this->get		= Inspekt::makeGetCage($config_file, $strict);
				break;
			case 'post':
				$this->post		= Inspekt::makePostCage($config_file, $strict);
				break;
			case 'cookie':
				$this->cookie	= Inspekt::makeCookieCage($config_file, $strict);
				break;
			case 'env':
				$this->env		= Inspekt::makeEnvCage($config_file, $strict);
				break;
			case 'files':
				$this->files	= Inspekt::makeFilesCage($config_file, $strict);
				break;
			case 'session':
				$this->session	= Inspekt::makeSessionCage($config_file, $strict);
				break;
			case 'server':
				$this->server	= Inspekt::makeServerCage($config_file, $strict);
				break;
			default:
				break;
		}
	}
}