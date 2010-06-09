<?php
/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.1
 */

/**
 * Provides the base object returned for printing
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
	private function dump($trace = false){

		$debug = Debug::called_from();
		$name = ($this->name ? $this->name : $this->print_type );

		print $this->line_end;
		printf('%s: from %s::%s() line %d',
					$name,
					$debug['caller']['class'],
					$debug['caller']['function'],
					$debug['caller']['line']);
		print $this->line_end;

		if($this->print_type == 'var_dump'){
			var_dump($this->val);
		}
		if($this->print_type == 'print_r'){
			print_r($this->val);
		}
		print $this->line_end;

		if($trace){
			print $name.' Backtrace: '.$this->line_end;
			print $debug['trace'];
		}
	}


	/**
	 *
	 */
	public function pre($trace = false){
		print '<pre>';
		$this->line_end = "\n";
		$this->p($trace);
		print '</pre>';
	}


	/**
	 *
	 */
	public function pre_v($trace = false){
		print '<pre>';
		$this->line_end = "\n";
		$this->v($trace);
		print '</pre>';
	}


	/**
	 *
	 */
	public function cli($trace = false){
		$this->line_end = "\n";
		$this->dump($trace);
	}


	/**
	 *
	 */
	public function html($hide = false, $trace = false){
		print $hide ? '<!--' : '';
		$this->line_end = $hide ? "\n" : "<br />";
		$this->dump($trace);
		print $hide ? '-->' : '';
	}


	/**
	 *
	 */
	public function p($trace = false){
		$this->print_type = 'print_r';
		$this->dump($trace);
	}


	/**
	 *
	 */
	public function v($trace = false){
		$this->print_type = 'var_dump';
		$this->dump($trace);
	}


	/**
	 *
	 */
	public function pre_v($trace = false){
		print '<pre>';
		$this->line_end = "\n";
		$this->v($trace);
		print '</pre>';
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
 * Provides helper methods for debugging
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
				$clean 	= Peregrine::sanitize($caller);
				if($ignore_phpunit && strpos(strtolower($clean->getPath('file')), 'phpunit') !== false){
					continue;
				}
				print $pos . ': ' . $clean->getPath('file').' - ' . $clean->getInt('line') . ' called ' . $clean->getElemId('class') . '::' . $clean->getElemId('function') . '();' . $line_end;
			}
		}

		print ($ignore_phpunit ? ' -- ignoring phpunit -- ' : '') . $line_end;

	}


	/**
	 *
	 * @param <type> $line_end
	 * @param <type> $ignore_phpunit
	 */
	static public function called_from($line_end = false, $ignore_phpunit = true){

		$line_end = $line_end ? $line_end : "\n";

		$db = debug_backtrace();
		$ret = array('trace'=>'','caller'=>array());

		foreach($db as $pos => $caller){
			if($pos > 0){
				$clean 	= Peregrine::sanitize($caller);
				if($ignore_phpunit && strpos(strtolower($clean->getPath('file')), 'phpunit') !== false){
					continue;
				}
				elseif(strpos(strtolower($clean->getPath('file')), 'debug') !== false){
					continue;
				}
				elseif(strpos(strtolower($clean->getElemId('class')), 'debugbase') !== false){
					continue;
				}

				if(empty($ret['caller'])){
					$ret['caller']['file'] = $clean->getPath('file');
					$ret['caller']['line'] = $db[($pos-1)]['line'];
					$ret['caller']['class'] = $clean->getElemId('class');
					$ret['caller']['function'] = $clean->getElemId('function');
				}

				$ret['trace'] .= $pos . ': ' . $clean->getPath('file').' - ' . $clean->getInt('line') . ' called ' . $clean->getElemId('class') . '::' . $clean->getElemId('function') . '();' . $line_end;

			}
		}

		return $ret;

	}
}
?>