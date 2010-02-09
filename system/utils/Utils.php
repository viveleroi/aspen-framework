<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.2
 */

/**
 * Provides a method of writing to a log file.
 * @package Aspen_Framework
 */
class Utils extends Library {


	/**
	 * Cleaner implode that gracefully handles null values
	 * 
	 * @param <type> $glue
	 * @param <type> $data
	 * @return <type>
	 */
	static public function implode($glue, $data){
		if($data){
			return implode($glue, $data);
		}
		return false;
	}


	/**
	 * The following code has been borrowed from CakePHP. We're very appreciative of their tools.
	 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
	 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
	 */


	/**
	 * Implements partial support for XPath 2.0. If $path is an array or $data is empty it the call is delegated to Utils::classicExtract.
	 *
	 * Currently implemented selectors:
	 * - /User/id (similar to the classic {n}.User.id)
	 * - /User[2]/name (selects the name of the second User)
	 * - /User[id>2] (selects all Users with an id > 2)
	 * - /User[id>2][<5] (selects all Users with an id > 2 but < 5)
	 * - /Post/Comment[author_name=john]/../name (Selects the name of all Posts that have at least one Comment written by john)
	 * - /Posts[name] (Selects all Posts that have a 'name' key)
	 * - /Comment/.[1] (Selects the contents of the first comment)
	 * - /Comment/.[:last] (Selects the last comment)
	 * - /Comment/.[:first] (Selects the first comment)
	 * - /Comment[text=/cakephp/i] (Selects the all comments that have a text matching the regex /cakephp/i)
	 * - /Comment/@* (Selects the all key names of all comments)
	 *
	 * Other limitations:
	 * - Only absolute paths starting with a single '/' are supported right now
	 *
	 * Warning: Even so it has plenty of unit tests the XPath support has not gone through a lot of real-world testing. Please report
	 * Bugs as you find them. Suggestions for additional features to imlement are also very welcome!
	 *
	 * @param string $path An absolute XPath 2.0 path
	 * @param string $data An array of data to extract from
	 * @param string $options Currently only supports 'flatten' which can be disabled for higher XPath-ness
	 * @return array An array of matched items
	 * @access public
	 * @static
	 */
	static function extract($path, $data = null, $options = array()) {
		if (is_string($data)) {
			$tmp = $data;
			$data = $path;
			$path = $tmp;
		}
		if (strpos($path, '/') === false) {
			return Utils::classicExtract($data, $path);
		}
		if (empty($data)) {
			return array();
		}
		if ($path === '/') {
			return $data;
		}
		$contexts = $data;
		$options = array_merge(array('flatten' => true), $options);
		if (!isset($contexts[0])) {
			$current = current($data);
			if ((is_array($current) && count($data) <= 1) || !is_array($current) || !Utils::numeric(array_keys($data))) {
				$contexts = array($data);
			}
		}
		$tokens = array_slice(preg_split('/(?<!=)\/(?![a-z-]*\])/', $path), 1);

		do {
			$token = array_shift($tokens);
			$conditions = false;
			if (preg_match_all('/\[([^=]+=\/[^\/]+\/|[^\]]+)\]/', $token, $m)) {
				$conditions = $m[1];
				$token = substr($token, 0, strpos($token, '['));
			}
			$matches = array();
			foreach ($contexts as $key => $context) {
				if (!isset($context['trace'])) {
					$context = array('trace' => array(null), 'item' => $context, 'key' => $key);
				}
				if ($token === '..') {
					if (count($context['trace']) == 1) {
						$context['trace'][] = $context['key'];
					}
					$parent = join('/', $context['trace']) . '/.';
					$context['item'] = Utils::extract($parent, $data);
					$context['key'] = array_pop($context['trace']);
					if (isset($context['trace'][1]) && $context['trace'][1] > 0) {
						$context['item'] = $context['item'][0];
					} else if(!empty($context['item'][$key])){
						$context['item'] = $context['item'][$key];
					} else {
						$context['item'] = array_shift($context['item']);
					}
					$matches[] = $context;
					continue;
				}
				$match = false;
				if ($token === '@*' && is_array($context['item'])) {
					$matches[] = array(
						'trace' => array_merge($context['trace'], (array)$key),
						'key' => $key,
						'item' => array_keys($context['item']),
					);
				} elseif (is_array($context['item']) && array_key_exists($token, $context['item'])) {
					$items = $context['item'][$token];
					if (!is_array($items)) {
						$items = array($items);
					} elseif (!isset($items[0])) {
						$current = current($items);
						if ((is_array($current) && count($items) <= 1) || !is_array($current)) {
							$items = array($items);
						}
					}

					foreach ($items as $key => $item) {
						$ctext = array($context['key']);
						if (!is_numeric($key)) {
							$ctext[] = $token;
							$token = array_shift($tokens);
							if (isset($items[$token])) {
								$ctext[] = $token;
								$item = $items[$token];
								$matches[] = array(
									'trace' => array_merge($context['trace'], $ctext),
									'key' => $key,
									'item' => $item,
								);
								break;
							} else {
								array_unshift($tokens, $token);
							}
						} else {
							$key = $token;
						}

						$matches[] = array(
							'trace' => array_merge($context['trace'], $ctext),
							'key' => $key,
							'item' => $item,
						);
					}
				} elseif (($key === $token || (ctype_digit($token) && $key == $token) || $token === '.')) {
					$context['trace'][] = $key;
					$matches[] = array(
						'trace' => $context['trace'],
						'key' => $key,
						'item' => $context['item'],
					);
				}
			}
			if ($conditions) {
				foreach ($conditions as $condition) {
					$filtered = array();
					$length = count($matches);
					foreach ($matches as $i => $match) {
						if (Utils::matches(array($condition), $match['item'], $i + 1, $length)) {
							$filtered[] = $match;
						}
					}
					$matches = $filtered;
				}
			}
			$contexts = $matches;

			if (empty($tokens)) {
				break;
			}
		} while(1);

		$r = array();

		foreach ($matches as $match) {
			if ((!$options['flatten'] || is_array($match['item'])) && !is_int($match['key'])) {
				$r[] = array($match['key'] => $match['item']);
			} else {
				$r[] = $match['item'];
			}
		}
		return $r;
	}


	/**
	 * This function can be used to see if a single item or a given xpath match certain conditions.
	 *
	 * @param mixed $conditions An array of condition strings or an XPath expression
	 * @param array $data  An array of data to execute the match on
	 * @param integer $i Optional: The 'nth'-number of the item being matched.
	 * @return boolean
	 * @access public
	 * @static
	 */
	static function matches($conditions, $data = array(), $i = null, $length = null) {
		if (empty($conditions)) {
			return true;
		}
		if (is_string($conditions)) {
			return !!Utils::extract($conditions, $data);
		}
		foreach ($conditions as $condition) {
			if ($condition === ':last') {
				if ($i != $length) {
					return false;
				}
				continue;
			} elseif ($condition === ':first') {
				if ($i != 1) {
					return false;
				}
				continue;
			}
			if (!preg_match('/(.+?)([><!]?[=]|[><])(.*)/', $condition, $match)) {
				if (ctype_digit($condition)) {
					if ($i != $condition) {
						return false;
					}
				} elseif (preg_match_all('/(?:^[0-9]+|(?<=,)[0-9]+)/', $condition, $matches)) {
					return in_array($i, $matches[0]);
				} elseif (!array_key_exists($condition, $data)) {
					return false;
				}
				continue;
			}
			list(,$key,$op,$expected) = $match;
			if (!isset($data[$key])) {
				return false;
			}

			$val = $data[$key];

			if ($op === '=' && $expected && $expected{0} === '/') {
				return preg_match($expected, $val);
			}
			if ($op === '=' && $val != $expected) {
				return false;
			}
			if ($op === '!=' && $val == $expected) {
				return false;
			}
			if ($op === '>' && $val <= $expected) {
				return false;
			}
			if ($op === '<' && $val >= $expected) {
				return false;
			}
			if ($op === '<=' && $val > $expected) {
				return false;
			}
			if ($op === '>=' && $val < $expected) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Checks to see if all the values in the array are numeric
	 *
	 * @param array $array The array to check.  If null, the value of the current Set object
	 * @return boolean true if values are numeric, false otherwise
	 * @access public
	 * @static
	 */
	static function numeric($array = null) {
		if (empty($array)) {
			return null;
		}

		if ($array === range(0, count($array) - 1)) {
			return true;
		}

		$numeric = true;
		$keys = array_keys($array);
		$count = count($keys);

		for ($i = 0; $i < $count; $i++) {
			if (!is_numeric($array[$keys[$i]])) {
				$numeric = false;
				break;
			}
		}
		return $numeric;
	}


	/**
	 * Gets a value from an array or object that is contained in a given path using an array path syntax, i.e.:
	 * "{n}.Person.{[a-z]+}" - Where "{n}" represents a numeric key, "Person" represents a string literal,
	 * and "{[a-z]+}" (i.e. any string literal enclosed in brackets besides {n} and {s}) is interpreted as
	 * a regular expression.
	 *
	 * @param array $data Array from where to extract
	 * @param mixed $path As an array, or as a dot-separated string.
	 * @return array Extracted data
	 * @access public
	 * @static
	 */
	static function classicExtract($data, $path = null) {
		if (empty($path)) {
			return $data;
		}
		if (is_object($data)) {
			$data = get_object_vars($data);
		}
		if (!is_array($data)) {
			return $data;
		}

		if (!is_array($path)) {
			$path = Utils::tokenize($path, '.', '{', '}');
		}
		$tmp = array();

		if (!is_array($path) || empty($path)) {
			return null;
		}

		foreach ($path as $i => $key) {
			if (is_numeric($key) && intval($key) > 0 || $key === '0') {
				if (isset($data[intval($key)])) {
					$data = $data[intval($key)];
				} else {
					return null;
				}
			} elseif ($key === '{n}') {
				if(is_array($data)){
					foreach ($data as $j => $val) {
						if (is_int($j)) {
							$tmpPath = array_slice($path, $i + 1);
							if (empty($tmpPath)) {
								$tmp[] = $val;
							} else {
								$tmp[] = Utils::classicExtract($val, $tmpPath);
							}
						}
					}
				}
				return $tmp;
			} elseif ($key === '{s}') {
				foreach ($data as $j => $val) {
					if (is_string($j)) {
						$tmpPath = array_slice($path, $i + 1);
						if (empty($tmpPath)) {
							$tmp[] = $val;
						} else {
							$tmp[] = Utils::classicExtract($val, $tmpPath);
						}
					}
				}
				return $tmp;
			} elseif (false !== strpos($key,'{') && false !== strpos($key,'}')) {
				$pattern = substr($key, 1, -1);

				foreach ($data as $j => $val) {
					if (preg_match('/^'.$pattern.'/s', $j) !== 0) {
						$tmpPath = array_slice($path, $i + 1);
						if (empty($tmpPath)) {
							$tmp[$j] = $val;
						} else {
							$tmp[$j] = Utils::classicExtract($val, $tmpPath);
						}
					}
				}
				return $tmp;
			} else {
				if (isset($data[$key])) {
					$data = $data[$key];
				} else {
					return null;
				}
			}
		}
		return $data;
	}

	
	/**
	 * Tokenizes a string using $separator, ignoring any instance of $separator that appears between $leftBound
	 * and $rightBound
	 *
	 * @param string $data The data to tokenize
	 * @param string $separator The token to split the data on
	 * @return array
	 * @access public
	 * @static
	 */
	static function tokenize($data, $separator = ',', $leftBound = '(', $rightBound = ')') {
		if (empty($data) || is_array($data)) {
			return $data;
		}

		$depth = 0;
		$offset = 0;
		$buffer = '';
		$results = array();
		$length = strlen($data);
		$open = false;

		while ($offset <= $length) {
			$tmpOffset = -1;
			$offsets = array(strpos($data, $separator, $offset), strpos($data, $leftBound, $offset), strpos($data, $rightBound, $offset));
			for ($i = 0; $i < 3; $i++) {
				if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset == -1)) {
					$tmpOffset = $offsets[$i];
				}
			}
			if ($tmpOffset !== -1) {
				$buffer .= substr($data, $offset, ($tmpOffset - $offset));
				if ($data{$tmpOffset} == $separator && $depth == 0) {
					$results[] = $buffer;
					$buffer = '';
				} else {
					$buffer .= $data{$tmpOffset};
				}
				if ($leftBound != $rightBound) {
					if ($data{$tmpOffset} == $leftBound) {
						$depth++;
					}
					if ($data{$tmpOffset} == $rightBound) {
						$depth--;
					}
				} else {
					if ($data{$tmpOffset} == $leftBound) {
						if (!$open) {
							$depth++;
							$open = true;
						} else {
							$depth--;
							$open = false;
						}
					}
				}
				$offset = ++$tmpOffset;
			} else {
				$results[] = $buffer . substr($data, $offset);
				$offset = $length + 1;
			}
		}
		if (empty($results) && !empty($buffer)) {
			$results[] = $buffer;
		}

		if (!empty($results)) {
			$data = array_map('trim', $results);
		} else {
			$data = array();
		}
		return $data;
	}
}
?>