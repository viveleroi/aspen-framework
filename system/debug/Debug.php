<?php
/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.1
 */

/**
 * @abstract Provides the base object returned for printing
 * @package Aspen_Framework
 */
class DebugBase {

	/**
	 * @var <type>
	 * @access private
	 */
	private $val;

	/**
	 * @var <type>
	 * @access private
	 */
	private $print_type;

	/**
	 * @var <type>
	 * @access private
	 */
	private $line_end;

	/**
	 * @var <type>
	 * @access private
	 */
	private $name;


	/**
	 *
	 * @param <type> $val
	 * @param <type> $name
	 * @param <type> $print_type
	 * @param <type> $line_end
	 */
	public function __construct($val, $name, $print_type = false, $line_end = false){
		$this->val = $val;
		$this->name = $name;
		$this->print_type = $print_type ? $print_type : 'var_dump';
		$this->line_end = $line_end ? $line_end : '\n';
	}


	/**
	 *
	 */
	private function dump(){

		print $this->line_end;
		print ($this->name ? $this->name : $this->print_type ).': '.$this->line_end;
		if($this->print_type == 'var_dump'){
			var_dump($this->val);
		}
		if($this->print_type == 'print_r'){
			print_r($this->val);
		}
		print $this->line_end;

	}


	/**
	 *
	 */
	public function pre(){
		print '<pre>';
		$this->line_end = "\n";
		$this->p();
		print '</pre>';
	}


	/**
	 *
	 */
	public function cli(){
		$this->line_end = "\n";
		$this->dump();
	}


	/**
	 *
	 */
	public function html(){
		print '<!--';
		$this->line_end = "<br />";
		$this->dump();
		print '-->';
	}


	/**
	 *
	 */
	public function p(){
		$this->print_type = 'print_r';
		$this->dump();
	}


	/**
	 *
	 */
	public function v(){
		$this->print_type = 'var_dump';
		$this->dump();
	}
}


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
//		if($this->APP->config('enable_firephp')){
//			include(dirname(__FILE__).DS.'firephp'.DS.'Fb.php');
//			include(dirname(__FILE__).DS.'firephp'.DS.'Firephp.php');
//		}
    }


	/**
	 *
	 * @return <type>
	 * @todo fix this static vs method issue
	 */
    static public function firephp(){
//		if($this->APP->config('enable_firephp')){
//			return Firephp::getInstance(true);
//		}
		return false;
    }


	/**
	 *
	 * @param <type> $val
	 * @param <type> $print_type
	 * @return DebugBase
	 */
	static public function dump($val, $name = false, $print_type = false){
		return new DebugBase($val, $name, $print_type);
	}


	/**
	 *
	 * @param <type> $line_end
	 * @param <type> $ignore_phpunit
	 */
	static public function who_called($line_end = false, $ignore_phpunit = true){

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

		print ($ignore_phpunit ? ' -- ignoring phpunit -- ' : '') . $line_end;

	}
}
?>