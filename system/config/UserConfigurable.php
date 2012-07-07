<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2012 Trellis Development, LLC
 * @since 		2.0
 */

/**
 * Description of UserConfigurable
 *
 * @author botskonet
 */
class UserConfigurable {
	
	/**
	 * Holds the config object
	 * @var type 
	 */
	protected $config;
	
	
	/**
	 * Calls correct env method
	 * @param type $config
	 * @param type $server
	 * @return null 
	 */
	public function __construct( $config, $server ) {
	

		/**
		 * We're able to specify different configuration parameters based
		 * on the context. If the auto-detected $server name matches any of
		 * the following cases, those configurations will be used 
		 */
		if(isset($this->production) && is_array($this->production) && in_array($server, $this->production)){
			define('ENVIRONMENT', 'production');
			$this->config = $this->production( $config );
		}
		else if(isset($this->staging) && is_array($this->staging) && in_array($server, $this->staging)){
			define('ENVIRONMENT', 'staging');
			$this->config = $this->staging( $config );
		}
		else {
			define('ENVIRONMENT', 'development');
			$this->config = $this->development( $config );
		}
	}
	
	
	/**
	 *
	 * @return type 
	 */
	public function getObject(){
		return $this->config;
	}
}