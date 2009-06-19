<?php
/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.1
 */


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

		// Firephp usage: http://www.firephp.org/HQ/Use.htm
		if($this->APP->config('enable_firephp')){
			include(dirname(__FILE__).DS.'firephp'.DS.'Fb.php');
			include(dirname(__FILE__).DS.'firephp'.DS.'Firephp.php');
		}
    }


	/**
	 *
	 * @return <type>
	 */
    static public function firephp(){
		if($this->APP->config('enable_firephp')){
			return Firephp::getInstance(true);
		}
		return false;
    }


	static public function dump($var, $name = 'var_dump', $line_end = false){

		$line_end = $line_end ? $line_end : "\n";


		print $line_end;
		print $name.': ';
		var_dump($var);
		print $line_end;

	}


	/**
	 *
	 * @param <type> $line_end
	 * @param <type> $ignore_phpunit
	 */
	static public function who_called($line_end = false, $ignore_phpunit = false){

		$line_end = $line_end ? $line_end : "\n";

		$db = debug_backtrace();

		print $line_end;

		foreach($db as $pos => $caller){
			if($pos > 0){
				$clean 	= Inspekt_Cage::Factory($caller);
				if($ignore_phpunit && strpos(strtolower($clean->getRaw('file')), 'phpunit') !== false){
					continue;
				}
				print $pos . ': ' . $clean->getRaw('file').' - ' . $clean->getRaw('line') . ' called ' . $clean->getRaw('class') . '::' . $clean->getRaw('function') . '();' . $line_end;
			}
		}

		print $line_end;

	}

}
?>