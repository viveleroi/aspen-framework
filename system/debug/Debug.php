<?php
/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.1
 */

// Firephp usage: http://www.firephp.org/HQ/Use.htm
include(dirname(__FILE__).DS.'firephp'.DS.'Fb.php');
include(dirname(__FILE__).DS.'firephp'.DS.'Firephp.php');

/**
 * @abstract Provides helper methods for debugging
 * @package Aspen_Framework
 */
class Debug {

    /**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	protected $APP;

    /**
     * @var object Holds the firephp system
     * @access private
     */
    public $firephp;


    /**
	 * @abstract Constructor
	 * @return Log
	 * @access private
	 */
	public function __construct(){
        $this->APP = get_instance();

        
        //$this->firephp()->error('here is a message');
    }

    public function firephp(){
        return Firephp::getInstance(true);
    }

}
?>