<?php
/**
 * Inspekt Supercage
 *
 * @author Ed Finkler <coj@funkatron.com>
 * @author Michael Botsko
 * @package Inspekt
 */

require_once 'Inspekt.php';
require_once 'Cage.php';

/**
 * @abstract The Supercage object wraps ALL of the superglobals
 * @package Inspekt
 */
Class Inspekt_Supercage {

	/**
	 * @var Inspekt_Cage The get cage
	 */
	public $get;

	/**
	 * @var Inspekt_Cage  The post cage
	 */
	public $post;

	/**
	 * The cookie cage
	 *
	 * @var Inspekt_Cage The cookie cage
	 */
	public $cookie;

	/**
	 * @var Inspekt_Cage The env cage
	 */
	public $env;

	/**
	 * @var Inspekt_Cage The files cage
	 */
	public $files;

	/**
	 * @var Inspekt_Cage The session cage
	 */
	public $session;

	/**
	 * @var Inspekt_Cage The server cage
	 */
	public $server;


	/**
	 * @abstract Returns the raw source of a cage
	 * @param string $type
	 * @return array
	 * @access public
	 */
	public function getRawSource($type = 'get'){
		return $this->{$type}->getRawSource();
	}

	
	/**
	 * @abstract 
	 * @param string  $config_file
	 * @param boolean $strict
	 * @return Inspekt_Supercage
	 * @access public
	 */
	 static public function Factory() {
		$sc	= new Inspekt_Supercage();
		$sc->_makeCages();
		$_REQUEST = null;
		return $sc;
	}


	/**
	 * @abstract Sets the cages into internal member variables
	 * @see Inspekt_Supercage::Factory()
	 * @param string  $config_file
	 * @param boolean $strict
	 * @access private
	 */
	public function _makeCages() {
		$this->get		= Inspekt::makeGetCage();
		$this->post		= Inspekt::makePostCage();
		$this->cookie	= Inspekt::makeCookieCage();
		$this->env		= Inspekt::makeEnvCage();
		$this->files	= Inspekt::makeFilesCage();
		$this->session	= Inspekt::makeSessionCage();
		$this->server	= Inspekt::makeServerCage();
	}
	
	
	/**
     * @abstract Refreshes a cage - avoid using in production
     */
	public function refreshCage($type = false, $config_file=NULL, $strict=TRUE){

		switch($type){
			case 'get':
				$this->get		= Inspekt::makeGetCage();
				break;
			case 'post':
				$this->post		= Inspekt::makePostCage();
				break;
			case 'cookie':
				$this->cookie	= Inspekt::makeCookieCage();
				break;
			case 'env':
				$this->env		= Inspekt::makeEnvCage();
				break;
			case 'files':
				$this->files	= Inspekt::makeFilesCage();
				break;
			case 'session':
				$this->session	= Inspekt::makeSessionCage();
				break;
			case 'server':
				$this->server	= Inspekt::makeServerCage();
				break;
			default:
				break;
		}
	}
}