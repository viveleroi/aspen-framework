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
		$this->line_end = $line_end;
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
	public function html($hide = false){
		print $hide ? '<!--' : '';
		$this->line_end = $hide ? "\n" : "<br />";
		$this->dump();
		print $hide ? '-->' : '';
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


	/**
	 *
	 * @param <type> $data
	 * @param <type> $caption
	 * @return <type>
	 */
	public function table(){

		$this->line_end = "<br />";

		$is_multi = $this->is_md_array($this->val);
		$html = '<strong>'.($this->name ? $this->name : $this->print_type ).'</strong>: '.$this->line_end;

		if(is_array($this->val)){

			$heads = $is_multi ? array_keys($is_multi) : array_keys($this->val);

			$html .= '<table>' . "\n";
			$html .= '<thead>' . "\n";

			if($this->name){
				$html .= '<caption>'.$this->name.'</caption>' . "\n";
			}

			$html .= '<th>' . implode("</th>\n<th>", $heads) . '</th>';
			$html .= '</thead>' . "\n";
			$html .= '<tbody>' . "\n";

			// Append table rows
			if($is_multi){
				foreach($this->val as $row){
					$html .= '<tr><td>' . implode("</td>\n<td>", $row) . '</td></tr>' . "\n";
				}
			} else {
				$html .= '<tr><td>' . implode("</td>\n<td>", $this->val) . '</td></tr>' . "\n";
			}

			$html .= '</tbody>' . "\n";
			$html .= '</table>' . "\n";

		}

		print $html;

	}


	/**
	 *
	 * @param <type> $a
	 * @return <type>
	 */
	private function is_md_array($a) {
		foreach ($a as $v) {
			if (is_array($v)) return $v;
		}
		return false;
	}
}


/**
 * @abstract Provides helper methods for debugging
 * @package Aspen_Framework
 */
class Debug {


	/**
	 *
	 * @return object
	 * // Firephp usage: http://www.firephp.org/HQ/Use.htm
	 */
    static public function firephp(){
		include_once(dirname(__FILE__).DS.'firephp'.DS.'Fb.php');
		include_once(dirname(__FILE__).DS.'firephp'.DS.'Firephp.php');
		return Firephp::getInstance(true);
    }


	/**
	 *
	 * @param <type> $val
	 * @param <type> $print_type
	 * @return DebugBase
	 */
	static public function dump($val, $name = false, $print_type = false, $line_end = false){
		return new DebugBase($val, $name, $print_type, $line_end);
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
				$per = Peregrine;
				$clean 	= $per->sanitize($caller);
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