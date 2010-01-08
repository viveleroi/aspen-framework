<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * This class manages our mysql sql query generation
 * @package Aspen_Framework
 */
class Model extends Library {

	/**
	 * @var array Holds an array of calculations we need to perform on the results
	 * @access private
	 */
	private $calcs = false;

	/**
	 * @var integer Current page = total results divided by per_page
	 * @access private
	 */
	private $current_page = false;

	/**
	 * @var array $errors Holds an array of field validation errors
	 * @access private
	 */
	private $errors = array();

	/**
	 * Flags a validation error
	 * @var boolean $error
	 * @access private
	 */
	private $error = false;

	/**
	 * Holds an existing record for change detection
	 * @var array
	 */
	private $existing_record = false;

	/**
	 * @var array
	 * @access private
	 */
	private $field_defaults = array();

	/**
	 * @var array Holds an array of security rules to apply to each field.
	 * @access private
	 */
	private $field_security_rules = array();

	/**
	 * @var array Holds an array of foreign key mappings
	 * @access private
	 */
	private $foreignkeys = array();

	/**
	 * @var string Holds the last executed query
	 * @access private
	 */
	private $last_query;

	/**
	 * @var string An array of tables to load which have a foreign key to current table
	 * @access private
	 */
	private $load_child_tables;

	/**
	 * @var boolean Toggles the pagination features
	 * @access private
	 */
	private $paginate = false;

	/**
	 * @var string undocumented class variable
	 * @access private
	 */
	private $parenth_start = false;

	/**
	 * @var integer Records per page for pagination
	 * @access private
	 */
	private $per_page = false;

	/**
	 * @var array Holds the type of query we're running, so we know what to return
	 * @access private
	 */
	private $query_type = 'select';

	/**
	 * @var array Whether or not to return a single record rather than the RECORDS array
	 * @access private
	 */
	private $return_single = false;

	/**
	 * @var object Holds the schema for the currently selected database
	 * @access private
	 */
	private $schema;

	/**
	 * @var string Holds our current SQL query
	 * @access private
	 */
	private $sql;

	/**
	 * @var string Identifies our currently select table
	 * @access private
	 */
	private $table;


	/**
	 * Contrucor, obtains an instance of the original app
	 * @return Model
	 * @access private
	 */
	public function __construct($table = false){
		parent::__construct();
		if($table){ $this->openTable($table); }
	}


//+-----------------------------------------------------------------------+
//| OPEN / SET / GET FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * Returns a model object or its child.
	 * @param string $table
	 * @return object
	 * @access public
	 */
	 final public function open($table, $id = false){

		$final_obj = false;

	 	if($table){

		 	$class = 'Model';
			$lang_module = false;

			// identify available model extensions
			$exts = $this->APP->getModelExtensions();
			if(is_array($exts)){
				if(array_key_exists($table, $exts)){
					$class = ucwords($table).'Model';
					if(isset($exts[$table]['module'])){
						$lang_module = $exts[$table]['module'];
					}
				}
			}

			if(class_exists($class)){
				if($lang_module){
					$this->APP->router->loadModuleLanguage($lang_module);
				}
				$final_obj = new $class($table);
			} else {
				$this->APP->error->raise(2, 'Failed loading model class: ' . $class, __FILE__, __LINE__);
				$final_obj = new Model($table);
			}

			if(is_object($final_obj)){
				$final_obj->select();
			}

			if($id){
				 // If a record id is set, let's just return the single record
				$final_obj = $final_obj->quickSelectSingle($id);
			}

		}

		return $final_obj;

	}


	/**
	 * Returns a model object or its child, and begins a basic SELECT (single) statement.
	 * @param string $table
	 * @return object
	 * @access public
	 */
	 final public function openSingle($table){
	 	$model = $this->open($table);
	 	if(is_object($model)){
	 		$model->select_single();
	 	}
		return $model;
	}


	/**
	 * Sets the current table and loads the table schema
	 * @param string $table
	 * @access private
	 * @return mixed
	 */
	private function openTable($table = false){
		$this->table = $table;
		$this->generateSchema();
		$this->generateForeignKeys();

		if(!is_array($this->schema)){
			$this->APP->error->raise(1, 'Failed generating schema for ' . $this->table . ' table.', __FILE__, __LINE__);
		}
	}


	/**
	 * Validates data is appropriate for the table before saving.
	 * @param array $fields
	 * @param mixed $primary_key
	 * @return object
	 * @access public
	 */
	public function validate($fields = false, $primary_key = false){

		$clean = false;

		// $fields must be an array or insert/update may not happen
		if(is_array($fields)){

			// if primary key has been set, we need to load an existing record
			if($primary_key && count($fields)){

				$this->existing_record = $record = $this->quickSelectSingle($primary_key);

				// merge the record with the incoming fields array
				// - any key in fields array overrides record
				if(is_array($record)){
					$fields = array_merge($record, $fields);
				}
			}

			// make an inspekt cage so we can verify data
			$clean 	= Peregrine::sanitize($fields);
			$schema = $this->getSchema();

			foreach($schema as $column){

				// if it's set, and a value is present, we must validate that
				// value against the database.
				// whether or not the value is present is up to the model extension, not this
				if($clean->isSetAndNotEmpty( $column->name )){


					/**
					 * Validate INTEGERs along with unsigned and maxlengths
					 */
					if(in_array($column->type, $this->APP->config('mysql_field_group_int'))){
						if(!$clean->getInt( $column->name )){
							$this->addError($column->name, 'Invalid db value. ' . $column->name . ' should be an integer.');
						} else {
							if($column->unsigned && !$clean->isGreaterThan( $column->name, -1 )){
								$this->addError($column->name, 'Invalid db value. ' . $column->name . ' may not be negative.');
							}
						}
					}


					/**
					 * Validate FLOATs along with unsigned and maxlengths
					 */
					if(in_array($column->type, $this->APP->config('mysql_field_group_dec'))){
						if(!$clean->getFloat( $column->name )){
							$this->addError($column->name, 'Invalid db value. ' . $column->name . ' should be a decimal or float.');
						} else {
							if($column->unsigned && !$clean->isGreaterThan( $column->name, -1 )){
								$this->addError($column->name, 'Invalid db value. ' . $column->name . ' may not be negative.');
							}
						}
					}


					/**
					 * Validate DATEs
					 */
					if(in_array($column->type, $this->APP->config('mysql_field_group_date'))){
						//if(!$clean->isDate( $date )){
							//$this->addError($column->name, 'Invalid db value. ' . $column->name . ' must be a date.');
						//}
					}


					/**
					 * Validate ENUMs
					 */
					if($column->type == 'enum'){
                        if(!$this->enumExists($column->name, $clean->getRaw( $column->name ))){
							$this->addError($column->name, 'Invalid db value. ' . $column->name . ' is not in list of acceptable values.');
						}
					}


					/**
					 * Rules to apply to all
					 */

					// maxlength
					if($column->max_length > 0 && strlen($clean->getRaw($column->name)) > $column->max_length){
						$this->addError($column->name, 'Invalid db value. ' . $column->name . ' exceeds maxlength.');
					}
				}
			}
		}

		return $clean;

	}


    /**
     * @sbstract Checks whether an ENUM value exists for the current field
     * @param <type> $field
     * @param <type> $enum_val
     * @return <type>
     */
    public function enumExists($field = false, $enum_vals = false){

        $found = true;

        $enums = false;
        if(isset($this->schema[strtoupper($field)])){
            $enums = $this->schema[strtoupper($field)]->enums;
        }

        if(is_array($enums)){

            // @todo make this a true lambda function with PHP 5.3
            $stripEnumChars = create_function('&$value', '$value = str_replace("\'", \'\', $value);');
            array_walk($enums, $stripEnumChars);

            // process loop of enum vals
            if(is_array($enum_vals)){
                foreach($enum_vals as $enum_val){
                    $found = $found ? in_array($enum_val, $enums) : false;
                }
            } else {
                $found = in_array($enum_vals, $enums);
            }
        }

        return $found;
    }


	/**
	 * Sets the default value if a field was not set or is empty
	 * @param <type> $field
	 * @param <type> $value
	 */
	public function setDefaultIfEmpty($field, $value){
		$this->field_defaults[$field] = $value;
	}


	/**
	 * Loads the current table schema.
	 * @access private
	 * @return array
	 */
	private function generateSchema(){
		$this->schema = $this->APP->db->MetaColumns($this->table, false);
	}


	/**
	 * Returns raw schema for the current table
	 * @return array
	 * @access public
	 */
	final public function getSchema(){
		return $this->schema;
	}


	/**
	 * Verifies that a field is present in the current db schema
	 * @param string $field
	 * @access public
	 */
	final public function inSchema($field){
		if(is_array($this->schema)){
			return array_key_exists(strtoupper($field), $this->schema);
		}
		return false;
	}


	/**
	 * Loads the current table foreign key mappings.
	 * @access private
	 * @return array
	 */
	private function generateForeignKeys(){

		$key_maps = array();

		// find all keys in the current table which connect it
		// to a key of another table
		$keys = $this->APP->db->MetaForeignKeys($this->table, false);
		if(is_array($keys)){
			foreach($keys as $table => $maps){
				foreach($maps as $table_field => $local_field){
					$key_maps[$local_field] = array($table => $table_field);
				}
			}
		}


		// attempt to find all tables which link to this tables primary key
		$fks = $this->APP->getChildForeignKeys();
		if(is_array($fks) && array_key_exists($this->table, $fks)){
			$children = $fks[$this->table];
			$key_maps[$this->getPrimaryKey()] = $children;
		}

		$this->foreignkeys = $key_maps;

	}


	/**
	 * Returns raw schema for the current table
	 * @return array
	 * @access public
	 */
	final public function getForeignKeys(){
		return $this->foreignkeys;
	}


	/**
	 * Returns the field marked as primary key for current table
	 * @return mixed
	 */
	final public function getPrimaryKey(){

		$schema = $this->getSchema();

		if(is_array($schema)){
			foreach($schema as $field){
				if($field->primary_key){
					return $field->name;
				}
			}
		}
		return false;
	}


	/**
	 * Sets the pagination toggle to true
	 * @access public
	 */
	public function enablePagination(){
		$this->paginate = true;
	}


	/**
	 * Returns the table status info
	 * @param string $table
	 * @return array
	 */
	public function showStatus($table = false){

		$table = $table ? $table : $this->table;

		if($table){
			$records = $this->query(sprintf('SHOW TABLE STATUS LIKE "%s"', $table));
			if($records->RecordCount()){
				while($record = $records->FetchRow()){
					return $record;
				}
			}
		}
		return false;
	}


	/**
	 * Returns the last run query
	 * @return string
	 * @access public
	 */
	public function getLastQuery($output = false){
		return $this->cleanQuery($this->last_query, $output);
	}


	/**
	 * Returns the last run query - aliases getLastQuery
	 * @return string
	 * @access public
	 */
	public function lq($output = false){
		return $this->getLastQuery($output);
	}


	/**
	 * Returns the query currently being built
	 * @return string
	 * @access public
	 */
	public function getBuildQuery($output = false){
		return $this->cleanQuery($this->writeSql(), $output);
	}


	/**
	 * Cleans the query for improved readability
	 * @param string $sql
	 * @return string
	 * @access private
	 */
	protected function cleanQuery($sql, $output = false){

		switch($output){
			case 'html':
				$sep = "<br>";
				break;
			default:
				$sep = "\n";
				break;
		}

		$break_words = array('WHERE','AND','OR','LIMIT','ORDER BY','GROUP BY','LEFT JOIN','RIGHT JOIN','UNION');
		foreach($break_words as $word){
			$sql = str_replace($word. ' ', $sep.$word.' ', $sql);
		}

		return $sql;
	}


//+-----------------------------------------------------------------------+
//| SECURITY RULES
//+-----------------------------------------------------------------------+


	/**
	 * Sets a security rule for data coming into a specific field
	 * @param string $field
	 * @param string $key
	 * @param string $value
	 * @access public
	 */
	final public function setSecurityRule($field, $key, $value){
		if($this->inSchema($field)){
			$this->field_security_rules[$field][$key] = $value;
			return;
		}
		return false;
	}


	/**
	 * Returns the security rule for a field and key
	 * @param string $field
	 * @param string $key
	 * @return mixed
	 */
	final public function getSecurityRule($field, $key){

		$rule_result = false;

		if($this->inSchema($field)){
			if(isset($this->field_security_rules[$field][$key])){
				$rule_result = $this->field_security_rules[$field][$key];
			}
		}

		return $rule_result;
	}


//+-----------------------------------------------------------------------+
//| SELECT GENERATING FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * Adds a new select statement to our query
	 * @param array $fields
	 * @param boolean $distinct
	 * @access private
	 */
	private function select_base($fields = false, $distinct = false){

		// begin the select, append SQL_CALC_FOUND_ROWS is pagination is enabled
		$this->sql['SELECT'] = $this->paginate ? 'SELECT SQL_CALC_FOUND_ROWS' : 'SELECT';

		// determine fields if any set
		$fields = is_array($fields) ? $fields : array('*');
		$official_fields = array();
		foreach($fields as $field){
			$official_fields[] = sprintf('%s.%s', $this->table, $field);
		}

		// append fields, append distinct if enabled
		$this->sql['FIELDS'] = ($distinct ? ' DISTINCT ' : '') . implode(', ', $official_fields);

		// set the from for our current table
		$this->sql['FROM'] = sprintf('FROM %s', $this->table);

	}


	/**
	 * Adds a new select statement to our query
	 * @param array $fields
	 * @param boolean $distinct
	 * @access public
	 */
	public function select($fields = false, $distinct = false){
		$this->return_single = false;
		$this->select_base($fields, $distinct);
	}


	/**
	 * Adds a new select statement to our query, but also forces a single result returned
	 *  outside of the RECORDS array
	 * @param array $fields
	 * @param boolean $distinct
	 * @access public
	 */
	public function select_single($fields = false, $distinct = false){
		$this->return_single = true;
		$this->select_base($fields, $distinct);
	}


	/**
	 * Adds a child table to inclusion in the results
	 * @param string $child_table
	 * @return boolean
	 * @access public
	 */
	public function select_children($child_table){
		foreach($this->foreignkeys as $field => $map){
			if(in_array($child_table, array_keys($map))){
				$this->load_child_tables[$field] = $child_table;
				return true;
			}
		}
		return false;
	}


	/**
	 * Adds an additional select field
	 * @param string $field
	 * @access public
	 */
	public function addSelectField($field){
		$this->sql['FIELDS'] .= sprintf(', %s', $field);
	}


	/**
	 * Adds an additional select field
	 * @param string $field
	 * @access public
	 */
	public function count($field = false, $as = false){
		$field = $field ? $field : $this->getPrimaryKey();
		$this->sql['FIELDS'] = sprintf('COUNT(%s)%s', $field, ($as ? 'AS '.$as : '' ) );
	}


	/**
	 * Generates a left join
	 * @param string $table
	 * @param string $key
	 * @param string $foreign_key
	 * @param array $fields Fields you want to return
	 */
	public function leftJoin($table, $key, $foreign_key, $fields = false, $from_table = false){

		$from_table = $from_table ? $from_table : $this->table;

		// if the user has included an as translation, use it
		if(strpos($table, " as ") > 0){

			$table_values = explode(" as ", $table);
			$table = $table_values[0];
			$as_table = $table_values[1];
			$as = ' as ' . $as_table;

		} else {
			$as = false;
			$as_table = $table;
		}

		// append the left join statement itself
		$this->sql['LEFT_JOIN'][] = sprintf('LEFT JOIN %s ON %s = %s.%s', $table . $as, $as_table.'.'.$key, $from_table, $foreign_key);

		// append the fields we've selected
		if(is_array($fields)){
			foreach($fields as $field){
				if(strpos($field, "SUM") === false){
					$this->sql['FIELDS'] .= sprintf(', %s.%s', $as_table, $field);
				} else {
					$this->sql['FIELDS'] .= sprintf(', %s', $field);
				}
			}
		}
	}


	/**
	 *
	 * @param string $table
	 * @return object
	 * @access public
	 */
	 public function selectSubquery_begin($table){
	 	$model = $this->open($table);
		return $model;
	}



	public function selectSubquery_end($model, $as){
		$sq = '('.$model->getBuildQuery().') AS ' . $as;
		$this->addSelectField($sq);
	}


//+-----------------------------------------------------------------------+
//| CONDITION GENERATING FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * undocumented function
	 * @return void
	 * @access private
	 **/
	public function parenthStart(){
		$this->parenth_start = true;
	}


	/**
	 * undocumented function
	 * @return void
	 * @access private
	 **/
	public function parenthEnd(){
		if(isset($this->sql['WHERE']) ){
			$this->sql['WHERE'][ (count($this->sql['WHERE'])-1) ] .= ')';
		}
		$this->parenth_start = false;
	}


	/**
	 * Forms the basis of the where clauses
	 * @param string $sprint_string
	 * @param string $field
	 * @param string $value
	 * @param string $match
	 * @access private
	 */
	protected function base_where($sprint_string = false, $field = false, $value = false, $match = 'AND'){

		$field = $field ? $field : $this->getPrimaryKey();
		$match = $match ? $match : 'AND';
		$match = $this->parenth_start ? $match.' (' : $match;

		$this->sql['WHERE'][] = sprintf($sprint_string,
											(isset($this->sql['WHERE']) ? $match : 'WHERE'.($this->parenth_start ? ' (' : '') ),
											$field,
											$this->APP->security->dbescape($value, $this->getSecurityRule($field, 'allow_html'))
										);

		$this->parenth_start = false;

	}

	
	/**
	 * Adds a custom condition to the array
	 * @param string $where
	 * @param string $match
	 * @access public
	 */
	public function whereCustom($where = false, $match = 'AND'){
		$prefix = (isset($this->sql['WHERE']) ? $match : 'WHERE'.($this->parenth_start ? ' (' : '') );
		$this->sql['WHERE'][] =  $prefix.' '.$where;
		$this->parenth_start = false;
	}


	/**
	 * Adds a standard where condition
	 * @param string $field
	 * @param mixed $value
	 * @param string $match
	 * @access public
	 */
	public function where($field = false, $value = false, $match = 'AND', $val_is_column_name = false){
		if($value === NULL){
			$this->whereIsNull($field, $match);
		} else {
			$str = $val_is_column_name ? '%s %s = %s' : '%s %s = "%s"';
			$this->base_where($str, $field, $value, $match);
		}
	}


	/**
	 * Adds a standard where not condition
	 * @param string $field
	 * @param mixed $value
	 * @param string $match
	 * @access public
	 */
	public function whereNot($field, $value, $match = 'AND', $val_is_column_name = false){
		$str = $val_is_column_name ? '%s %s != %s' : '%s %s != "%s"';
		$this->base_where($str, $field, $value, $match);
	}


	/**
	 * Adds a WHERE ... IS NULL condition
	 * @param string $field
	 * @param string $match
	 * @access public
	 */
	public function whereIsNull($field = false, $match = 'AND'){
		$this->base_where('%s %s IS NULL', $field, $match);
	}


	/**
	 * Adds a WHERE ... IS  NOT NULL condition
	 * @param string $field
	 * @param string $match
	 * @access public
	 */
	public function whereIsNotNull($field = false, $match = 'AND'){
		$this->base_where('%s %s IS NOT NULL', $field, $match);
	}


	/**
	 * Adds a standard where like %% condition
	 * @param string $field
	 * @param mixed $value
	 * @param string $match
	 * @access public
	 */
	public function whereLike($field, $value, $match = 'AND'){
		$this->base_where('%s %s LIKE "%%%s%%"', $field, $value, $match);
	}


	/**
	 * Adds a series of WHERE LIKE values that mimic a search query for multiple fields
	 * @param array $fields
	 * @param mixed $value
	 * @access public
	 */
	public function whereLikeSearch($fields, $value, $match = 'AND'){
		$this->parenthStart();
		if(is_array($fields)){
			foreach($fields as $field){
				$this->base_where('%s %s LIKE "%%%s%%"', $field, $value, $match);
				$match = 'OR';
			}
		}
		$this->parenthEnd();
	}


	/**
	 * Searches for values between $start and $end
	 * @param string $field
	 * @param mixed $start
	 * @param string $end
	 * @param string $match
	 * @access public
	 */
	public function whereBetween($field, $start, $end, $match = 'AND'){
		$this->base_where('%s %s BETWEEN "'.$start.'" AND "'.$end.'"', $field, false, $match);
	}


	/**
	 * Adds a standard where greater than condition
	 * @param string $field
	 * @param mixed $value
	 * @param string $match
	 * @access public
	 */
	public function whereGreaterThan($field, $value, $match = 'AND'){
		$this->base_where('%s %s > "%s"', $field, $value, $match);
	}


	/**
	 * Adds a standard where greater than or is equal to condition
	 * @param string $field
	 * @param mixed $value
	 * @param string $match
	 * @access public
	 */
	public function whereGreaterThanEqualTo($field, $value, $match = 'AND'){
		$this->base_where('%s %s >= "%s"', $field, $value, $match);
	}


	/**
	 * Adds a standard where less than condition
	 * @param string $field
	 * @param mixed $value
	 * @param string $match
	 * @access public
	 */
	public function whereLessThan($field, $value, $match = 'AND'){
		$this->base_where('%s %s < "%s"', $field, $value, $match);
	}


	/**
	 * Adds a standard where less than or is equal to condition
	 * @param string $field
	 * @param mixed $value
	 * @param string $match
	 * @access public
	 */
	public function whereLessThanEqualTo($field, $value, $match = 'AND'){
		$this->base_where('%s %s <= "%s"', $field, $value, $match);
	}


	/**
	 * Finds timestamps equal to today
	 * @param string $field
	 * @param boolean $include_today
	 * @param string $match
	 * @access public
	 */
	public function whereToday($field, $match = 'AND'){
		$this->base_where('%s TO_DAYS(%s) = TO_DAYS(NOW())', $field, false, $match);
	}


	/**
	 * Finds timestamps prior to today
	 * @param string $field
	 * @param boolean $include_today
	 * @param string $match
	 * @access public
	 */
	public function whereBeforeToday($field, $include_today = true, $match = 'AND'){
		$this->base_where('%s TO_DAYS(%s) <'.($include_today ? '=' : '').' TO_DAYS(NOW())', $field, false, $match);
	}


	/**
	 * Finds timestamps before the current moment
	 * @param string $field
	 * @param boolean $include_today
	 * @param string $match
	 * @access public
	 */
	public function wherePast($field, $include_today = false, $match = 'AND'){
		$this->sql['WHERE'][] = sprintf('%s UNIX_TIMESTAMP(%s) %s< UNIX_TIMESTAMP(NOW())', (isset($this->sql['WHERE']) ? $match : 'WHERE'), $field, ($include_today ? '=' : ''));
	}


	/**
	 * Finds timestamps after today
	 * @param string $field
	 * @param boolean $include_today
	 * @param string $match
	 * @access public
	 */
	public function whereAfterToday($field, $include_today = false, $match = 'AND'){
		$this->base_where('%s TO_DAYS(%s) >'.($include_today ? '=' : '').' TO_DAYS(NOW())', $field, false, $match);
	}


	/**
	 * Finds timestamps after the current moment
	 * @param string $field
	 * @param boolean $include_today
	 * @param string $match
	 * @access public
	 */
	public function whereFuture($field, $include_today = false, $match = 'AND'){
		$this->sql['WHERE'][] = sprintf('%s UNIX_TIMESTAMP(%s) %s> UNIX_TIMESTAMP(NOW())', (isset($this->sql['WHERE']) ? $match : 'WHERE'), $field, ($include_today ? '=' : ''));
	}


	/**
	 * Finds timestamps in the last $day_count days
	 * @param string $field
	 * @param string $day_count
	 * @param boolean $include_range
	 * @param string $match
	 * @access public
	 */
	public function inPastXDays($field, $day_count = 7, $include_range = true, $match = 'AND'){
		$this->base_where('%s TO_DAYS(NOW()) - TO_DAYS(%s) '.($include_range ? '<' : '').'= ' . $day_count, $field, false, $match);
	}


//+-----------------------------------------------------------------------+
//| AUTO-FILTER (auto-condition) FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * Handles incoming filter params in url to add automated conditions to query
	 * @param array $filters
	 * @param array $allowed_filter_keys
	 * @param array $disabled_filters
	 * @return array
	 * @access public
	 */
	public function addFilters($filters = false, $location_key = false, $allowed_filter_keys = false, $disabled_filters = false){

		// Create a base array of the current schema
		$table_base_fields = array('keyword_search'=>false);
		$table_schema = array_keys($this->getSchema());
		foreach($table_schema as $key){
			$table_base_fields[strtolower($key)] = false;
		}

		$user_id				= $this->APP->params->session->getInt('user_id', NULL);
		$using_filters			= false;
		$location_key			= $location_key ? $location_key : ($this->APP->router->getSelectedModule() . ':' . $this->APP->router->getSelectedMethod());
		$disabled_filters		= $disabled_filters ? $disabled_filters : array();
		$allowed_filter_keys	= $allowed_filter_keys ? $allowed_filter_keys : array();

		// check GET or POST for any filter overrides
		// otherwise, check the config table
		if($this->APP->params->get->getRaw('filter')){
			$filters = $this->APP->params->get->getRaw('filter');
		}
		elseif($this->APP->params->post->getRaw('filter')){
			$filters = $this->APP->params->post->getRaw('filter');
		}
		elseif($named = $this->APP->params->get->getRaw('named-filter')){
			$named = $this->APP->router->decodeForRewriteUrl($named);
			$filters = $this->APP->settings->getConfig('filter.named.'.$named, $user_id);
			$filters = unserialize($filters);
		} else {
			$filters = $this->APP->settings->getConfig('filter.'.$location_key, $user_id);
			$filters = unserialize($filters);
		}

		// look for a save-as named variable
		// if it's set, we'll store this as a named filter
		$filter_name = false;
		if($this->APP->params->get->getRaw('filter-save-as')){
			$filter_name = $this->APP->params->get->getRaw('filter-save-as');
		}
		elseif($this->APP->params->post->getRaw('filter-save-as')){
			$filter_name = $this->APP->params->post->getRaw('filter-save-as');
		}

		// over-write the table keys with filters
		if(is_array($filters)){
			$filters = array_merge($table_base_fields, $filters);
		} else {
			$filters = $table_base_fields;
		}

		// set base allowed filters if not set
		if(empty($allowed_filter_keys) && is_array($filters)){
			$allowed_filter_keys = array_keys($filters);
		}

		// loop filters and append to query
		if(is_array($filters)){
			foreach($filters as $field => $value){
				if(
					$value != '' &&
					in_array($field, $allowed_filter_keys) &&
					!in_array($field, $disabled_filters)
					){

					$value_array = false;

					// If the value is an array
					if(is_array($value) && count($value)){
						$value_array = $value;
					} else {

						// Looks for string chars the simulate an array
						if(strpos($value, ' and ') > 0){
							$value_array = explode(" and ", $value);
						}
						elseif(strpos($value, ' & ') > 0){
							$value_array = explode(" & ", $value);
						}
						elseif(strpos($value, ',') > 0){
							$value_array = explode(",", $value);
						}
						elseif(strpos($value, ' or ') > 0){
							$value_array = explode(" or ", $value);
						} else {
							$value_array = array($value);
						}
					}

					if(is_array($value_array) && count($value_array) && !empty($value_array[0])){
						$count = 1;
						$this->parenthStart();
						foreach($value_array as $match){
							if(!empty($match)){

								$using_filters = true;

								// if keyword search
								if($field == 'keyword_search'){
									$this->match($match);
								}

								// Match operators inside the filter
								elseif(substr($match, 0, 1) == "!"){
									$this->whereNot($field, str_replace("!", "", $match));
								}
								elseif(substr($match, 0, 1) == ">"){
									$this->whereGreaterThan($field, str_replace(">", "", $match));
								}
								elseif(substr($match, 0, 2) == ">="){
									$this->whereGreaterThanEqualTo($field, str_replace(">=", "", $match));
								}
								elseif(substr($match, 0, 1) == "<"){
									$this->whereLessThanEqualTo($field, str_replace("<", "", $match));
								}
								elseif(substr($match, 0, 2) == "<="){
									$this->whereLessThanEqualTo($field, str_replace("<=", "", $match));
								} else {
									if(is_int((int)$match)){
										$this->where($field, trim($match), ($count == 1 ? 'AND' : 'OR'));
									} else {
										$this->whereLike($field, trim($match), ($count == 1 ? 'AND' : 'OR'));
									}
								}

								$count++;

							}

						}
						$this->parenthEnd();
					}
				}

				if($value === 0){
					$this->where($field, 0);
				}
			}
		}

		// save the filters to the config table
		$this->APP->settings->setConfig('filter.'.$location_key, serialize($filters), $user_id);

		// if a save-as name is set, store that filter too
		if($filter_name){
			$this->APP->settings->setConfig('filter.named.'.$filter_name, serialize($filters), $user_id);
		}

		define('MODEL_FILTER_IN_USE', $using_filters);

		return $filters;

	}


//+-----------------------------------------------------------------------+
//| SORT AND MATCH GENERATING FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * Adds a sort order, optionally pulls from saved prefs
	 * @param string $field
	 * @param string $dir
	 * @param string $sort_location
	 * @access public
	 */
	public function orderBy($field = false, $dir = false, $sort_location = false){

		$field = $field ? $field : $this->table.'.'.$this->getPrimaryKey();

		// ensure sort by field has been selected
		if(strpos($this->sql['FIELDS'], '*') === false){
			// explode by fields if any
			$fields = explode(',', $this->sql['FIELDS']);
			if(is_array($fields)){
				// remove any table references
				foreach($fields as $key => $tmp_field){
					$fields[$key] = preg_replace('/(.*)\./', '', $tmp_field);
				}

				// remove any table reference from our field
				$tmp_field = preg_replace('/(.*)\./', '', $field);

				// check if our field is in the array of fields
				if(!in_array($tmp_field, $fields)){
					// if not, go with the first item
					$field = $fields[0];
				}
			}
		}

		$sort['sort_by'] 		= $field;
		$sort['sort_direction'] = $dir = $dir ? $dir : 'ASC';

		if($sort_location){
			$sort = $this->APP->prefs->getSort($sort_location, false, $field, $dir);
		}

		if(empty($sort['sort_by'])){
			$sort['sort_by'] = $field;
		}

		if(empty($sort['sort_direction'])){
			$sort['sort_direction'] = $dir;
		}

		// verify the field exists, if muliple fields present, skip
		if(strpos($sort['sort_by'], ',') === false && strpos($sort['sort_by'], 'ASC') === false){
			$sort['sort_by'] = array_key_exists(strtoupper($field), $this->getSchema()) || strpos($this->sql['FIELDS'], $field) ? $sort['sort_by'] : $this->table.'.'.$this->getPrimaryKey();
		}
		$this->sql['ORDER'] = sprintf("ORDER BY %s %s", $sort['sort_by'], $sort['sort_direction']);

	}


	/**
	 * Limits the results returned
	 * @param integer $start
	 * @param integer $limit
	 * @access public
	 */
	public function limit($start = 0,$limit = 25){
		$start = $start < 0 ? 0 : $start;
		$this->sql['LIMIT'] = sprintf('LIMIT %s,%s', $start, abs($limit));
	}


	/**
	 * Adds a fulltext index match function
	 * @param string $search
	 * @param array $fields
	 * @param string $match
	 * @access public
	 */
	public function match($search, $fields = false, $match = 'AND'){

		$search = $this->APP->security->dbescape($search);

		if(!$fields){

			$fields = array();

			foreach($this->schema as $field){
				if(in_array($field->type, $this->APP->config('mysql_field_group_text'))){
					$fields[] = $field->name;
				}
			}
		}

		if(is_array($fields) && count($fields)){
			$this->sql['WHERE'][] = sprintf('%s MATCH(%s) AGAINST ("%s" IN BOOLEAN MODE)', (isset($this->sql['WHERE']) ? $match : 'WHERE'.($this->parenth_start ? ' (' : '') ), implode(",", $fields), $search);
			$this->addSelectField( sprintf('MATCH(%s) AGAINST ("%s" IN BOOLEAN MODE) as match_relevance', implode(",", $fields), $search) );
		}
	}


	/**
	 * Sets the limit for pagination page numbers
	 * @param integer $current_page
	 * @param integer $per_page
	 * @access public
	 */
	public function paginate($per_page = 25,$current_page = false){

		$this->current_page = $current_page ? $current_page : 1;
		$this->per_page = $per_page;

		$query_offset = ($current_page - 1) * abs($per_page);
		$this->limit($query_offset,$per_page);
	}


	/**
	 * Sets a group by
	 * @param string $field
	 * @access public
	 */
	public function groupBy($field){
		$this->sql['GROUP'] = sprintf("GROUP BY %s", $field);
	}


//+-----------------------------------------------------------------------+
//| QUERY EXECUTION FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * Builds the query we've designed from the above functions
	 * @return string
	 * @access private
	 */
	private function writeSql(){

		$sql = '';

		// generate the insert query
		if(isset($this->sql['INSERT'])){
			$this->query_type = 'insert';
			$sql = $this->sql['INSERT'];
		}

		// generate the update query
		elseif(isset($this->sql['UPDATE'])){
			$this->query_type = 'update';
			$sql = $this->sql['UPDATE'];
		}

		// generate the select query
		elseif(isset($this->sql['SELECT'])){

			$this->query_type = 'select';

			$sql .= '' . $this->sql['SELECT'];
			$sql .= ' ' . $this->sql['FIELDS'];
			$sql .= ' ' . $this->sql['FROM'];

			if(isset($this->sql['LEFT_JOIN']) && array($this->sql['LEFT_JOIN'])){
				$sql .= ' ' . implode(" ", $this->sql['LEFT_JOIN']);
			}

			if(isset($this->sql['WHERE']) && array($this->sql['WHERE'])){
				$sql .= ' ' . implode(" ", $this->sql['WHERE']);
			}

			$sql .= ' ' . (isset($this->sql['GROUP']) ? $this->sql['GROUP'] : '');

			// if no order set, generate one
			if(!isset($this->sql['ORDER'])){
				$this->orderBy();
			}

			$sql .= ' ' . (isset($this->sql['ORDER']) ? $this->sql['ORDER'] : '');

			$sql .= ' ' . (isset($this->sql['LIMIT']) ? $this->sql['LIMIT'] : '');

		}

		else {

			$this->select();
			$sql = $this->writeSql();

		}

		return $sql;

	}


	/**
	 * A wrapper for running a query directly to the db, and provided the results directly to the caller
	 * @param string $query
	 * @return object
	 * @access public
	 */
	public function query($query = false){

		$results = false;

		if($query && !$this->error()){

			$this->last_query = $query;

			if(!$results = $this->APP->db->Execute($query)){
				// we don't want every query to show as failure here, so we use the true last location
				$back = debug_backtrace();
				$file = strpos($back[0]['file'], 'Model.php') ? $back[1]['file'] : $back[0]['file'];
				$line = strpos($back[0]['file'], 'Model.php') ? $back[1]['line'] : $back[0]['line'];

				$this->APP->error->raise(2, $this->APP->db->ErrorMsg() . "\nSQL:\n" . $query, $file, $line);

			} else {
				if($this->APP->config('log_verbosity') < 3){
					$this->APP->log->write($query);
				}
			}
		}

		$this->reset();

		return $results;

	}


	/**
	 * Runs the generated query and appends any additional info we've selected
	 * @param string $key_field Field value to use for array element key values
	 * @param string $sql Optional sql query replacing any generated
	 * @return array
	 * @access public
	 */
	public function results($key_field = false, $sql = false){

		$sql = $sql ? $sql : $this->writeSql();

		// if we're doing a select
		if($this->query_type == 'select'){

			$records = array();
			$records['RECORDS'] = array();

			if($results = $this->query($sql)){

				$key = $key_field ? $key_field : $this->getPrimaryKey();

				if($results->RecordCount()){
					while($result = $results->FetchRow()){

						// Load in data from any selected child tables
						if(!empty($this->load_child_tables)){
							foreach($this->load_child_tables as $key => $child_table){

								// get the foreign key field name
								$fk_field = $this->foreignkeys[$key][$child_table];

								// run query on child table to find all records
								$child = $this->open($child_table);
								$child->where($fk_field, $result[$key]);
								$result[$child_table] = $child->results();

							}
						}

						if(isset($result[$key]) && !isset($records['RECORDS'][$result[$key]])){
	                    	$records['RECORDS'][$result[$key]] = $result;
	                    } else {
	                    	$records['RECORDS'][] = $result;
	                    }
					}
				} else {

					$records['RECORDS'] = false;

				}
			} else {

				$records['RECORDS'] = false;

			}

			$this->tmp_records = $records;

			// perform any calcs
			if($this->calcs){
				foreach($this->calcs['TOTAL'] as $field){
					$records[strtoupper('TOTAL_' . $field)] = $this->calcTotal($field);
				}
			}

			// if any pagination, return found rows
			if($this->paginate){
				$results = $this->query('SELECT FOUND_ROWS()');
				$records['TOTAL_RECORDS_FOUND'] = $results->fields['FOUND_ROWS()'];
				$records['CURRENT_PAGE'] = $this->current_page;
				$records['RESULTS_PER_PAGE'] = $this->per_page;
				$records['TOTAL_PAGE_COUNT'] = ceil($records['TOTAL_RECORDS_FOUND'] / $this->per_page);
			} else {
				$records['TOTAL_RECORDS_FOUND'] = ($records['RECORDS'] ? count($records['RECORDS']) : 0);
			}

			// If return single set, grab first array item
			if($this->return_single){
				$records = $records['RECORDS'] ? array_shift($records['RECORDS']) : false;
			}

			$this->tmp_records = false;

			return $records;

		}

		// if we're doing an INSERT
		if($this->query_type == 'insert'){
			if($this->query($sql)){
				return $this->APP->db->Insert_ID();
			}
		}

		// if we're doing an UPDATE
		if($this->query_type == 'update'){
			if($this->query($sql)){
				return true;
			} else {
				return false;
			}
		}


		$this->reset();
		return false;

	}


	/**
	 * Returns a single field, single-record value from a query
	 * @param string $sql
	 * @param string $return_field
	 * @return mixed
	 * @access public
	 */
	public function quickValue($sql = false, $return_field = 'id'){
		$result = $this->query($sql);
		if($result->RecordCount()){
			while($row = $result->FetchRow()){
				return isset($row[$return_field]) ? $row[$return_field]  : false;
			}
		}
		return false;
	}


	/**
	 * Clears any generated queries
	 * @access public
	 */
	final public function reset(){
		$this->sql		= false;
		$this->error	= false;
		$this->errors	= array();
	}


//+-----------------------------------------------------------------------+
//| AUTO-QUERY-WRITING FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * Generates a quick select statement for a single record
	 * @param integer $id
	 * @param string $field
	 * @return array
	 * @access public
	 */
	public function quickSelectSingle($id = false, $field = false){
		$field = $field ? $field : $this->getPrimaryKey();
		$this->select_single();
		$this->where($field, $id);
		return $this->results($field);
	}


	/**
	 * Generates a quick select statement for a single record and returns the result as xml
	 * @param integer $id
	 * @return string
	 * @access public
	 */
	public function quickSelectSingleToXml($id = false){
		return $this->APP->xml->arrayToXml( $this->quickSelectSingle($id) );
	}


	/**
	 * Generates and executes a select query
	 * @param integer $id
	 * @param string $field_name
	 * @return boolean
	 * @access public
	 */
	public function delete($id = false, $field_name = false){
		$field_name = $field_name ? $field_name : $this->getPrimaryKey();
		if($this->inSchema($field_name)){

			$this->select();
			$this->where($field_name, $id);
			$records = $this->results();

			if($records['RECORDS']){
				foreach($records['RECORDS'] as $del){
					$this->sql['DELETE'] = sprintf('DELETE FROM %s WHERE %s = "%s"', $this->table, $this->getPrimaryKey(), $del['id']);
					$result = (bool)$this->query($this->sql['DELETE']);
					if($result){
						$this->activity_detect_changes('delete', $del['id'], false);
						return $result;
					}
				}
			}
		}
		return false;
	}


	/**
	 * Drops a table completely
	 * @param string $table
	 * @return boolean
	 * @access public
	 */
	public function drop(){
		if($this->table){
			return $this->query(sprintf('DROP TABLE %s', $this->table));
		}
		return false;
	}


	/**
	 * Duplicates records using INSERT... SELECT...
	 * @param mixed $id
	 * @param string $field_name
	 * @param string $select_table
	 * @return integer
	 * @access public
	 */
	public function duplicate($id, $field_name = false){

		$fields = $this->getSchema();

		foreach($fields as $field){
			if(!$field->auto_increment){
				$field_names[] = $field->name;
			}
		}

		$key = $this->getPrimaryKey();
		$field_name = $field_name ? $field_name : $key;

		$sql = sprintf('INSERT INTO %s (%s) SELECT %2$s FROM %s WHERE %s = %s ORDER BY %s',
							$this->table,
							implode(', ', $field_names),
							$this->table,
							$field_name,
							$id,
							$key);

		$this->query($sql);

		return $this->APP->db->Insert_ID();

	}


//+-----------------------------------------------------------------------+
//| END-RESULT MANIPULATION FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * Adds a field calculation to db results
	 * @param string $field
	 * @param string $type
	 * @access public
	 */
	public function addCalc($field, $type = 'total'){
		$this->calcs[strtoupper($type)][] = $field;
	}


	/**
	 * Calculates the total for a field in the resultset
	 * @param array $records
	 * @param string $field
	 * @return float
	 * @access private
	 */
	protected function calcTotal($field){

		$total = 0;

		if(is_array($this->tmp_records['RECORDS'])){
			foreach($this->tmp_records['RECORDS'] as $record){
				$total += isset($record[$field]) ? $record[$field] : 0;
			}
		}

		return $total;

	}


	/**
	 * Creates a basic table with the results
	 * @param array $row_names
	 * @param array $ignore_fields
	 * @return string
	 * @access public
	 */
	public function createHtmlTable($row_names = false, $ignore_fields = false){

		$row_names = is_array($row_names) ? $row_names : array();

		$html = '<table>' . "\n";

		foreach($this->schema as $field){
			if(!$field->primary_key && !in_array($field->name, $ignore_fields)){

				$name = isset($row_names[$field->name]) ? $row_names[$field->name] : $field->name;

				// clean name for row title
				$name = ucwords(str_replace("_", " ", $name));

				$html .= sprintf('<tr><td><b>%s:</b></td><td>%s</td></tr>' . "\n", $name, $this->APP->form->cv($field->name));
			}
		}

		$html .= '</table>' . "\n";

		return $html;

	}


//+-----------------------------------------------------------------------+
//| INSERT / UPDATE FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * Allows instructions to be written prior to INSERT query in custom
	 * model libraries.
	 *
	 * @param array $fields
	 * @return array
	 */
	public function before_insert($fields){
		return $fields;
	}


	/**
	 * Allows instructions to be written after an INSERT query in custom
	 * model libraries.
	 *
	 * @param integer $result
	 * @return integer
	 */
	public function after_insert($result, $values){
		return $result;
	}


	/**
	 * Generates an INSERT query and auto-executes it
	 * @param array $fields
	 * @return integer
	 * @access public
	 */
	public function insert($fields = false){

		// set default values if prompted from extension install / update funcs
		$fields = $this->getDefaults($fields);

		// Pass through the before_insert function
		$fields = $this->before_insert($fields);

		if($this->validate($fields)){

			if($this->table && is_array($fields)){

				$ins_fields = '';
				$ins_values = '';

				foreach($fields as $field_name => $field_value){
					if($this->inSchema($field_name)){

						$ins_fields .= ($ins_fields == '' ? '' : ', ') . $this->APP->security->dbescape($field_name);

						if(is_null($field_value)){
							$ins_values .= (empty($ins_values) ? '' : ', ') . 'NULL';
						} else {
							$ins_values .= (empty($ins_values) ? '' : ', ') . '"' . $this->APP->security->dbescape($field_value, $this->getSecurityRule($field_name, 'allow_html')) . '"';
						}

					}
				}

				$this->sql['INSERT'] = sprintf('INSERT INTO %s (%s) VALUES (%s)',
									$this->APP->security->dbescape($this->table),
									$ins_fields,
									$ins_values
								);

			}

			// Pass through the after_insert function
			$result = $this->results();

			// run the activity log
			if($result){
				$this->activity_detect_changes('insert', $result, $fields);
			}

			$this->after_insert($result, $fields);

			return $result;

		}

		return false;

	}


	/**
	 * Allows instructions to be written prior to UPDATE query in custom
	 * model libraries.
	 *
	 * @param array $fields
	 * @return array
	 */
	public function before_update($fields){
		return $fields;
	}


	/**
	 * Allows instructions to be written after an UPDATE query in custom
	 * model libraries.
	 *
	 * @param integer $result
	 * @return integer
	 */
	public function after_update($result, $where_value, $where_field, $values, $old_values){
		return $result;
	}


	/**
	 * Auto-generates and executes an UPDATE query
	 * @param array $fields
	 * @param mixed $where_value
	 * @param string $where_field
	 * @return boolean
	 * @access public
	 */
	public function update($fields = false, $where_value = false, $where_field = false ){

		// set default values if prompted from extension install / update funcs
		$fields = $this->getDefaults($fields);

		// Pass value through to the before update function
		$fields = $this->before_update($fields);

		// if where_value is our primary key, we should load the record first
		// so that we can use those existing values to pass validation more quickly
		$update_id = false;
		if(!$where_field || $where_field == $this->getPrimaryKey()){
			$where_field = $this->getPrimaryKey();
			$update_id = $where_value;
		}

		// if validation passes, build and run the query
		if($this->validate($fields, $update_id)){

			if($this->table && is_array($fields)){

				$upd_fields = '';
				foreach($fields as $field_name => $field_value){
					if($this->inSchema($field_name)){
						if(is_null($field_value)){
							$upd_fields .= ($upd_fields == '' ? '' : ', ') . $this->APP->security->dbescape($field_name) . ' = NULL';
						} else {
							$upd_fields .= ($upd_fields == '' ? '' : ', ') . $this->APP->security->dbescape($field_name) . ' = "' . $this->APP->security->dbescape($field_value, $this->getSecurityRule($field_name, 'allow_html')) . '"';
						}
					}
				}

				$this->sql['UPDATE'] = sprintf('UPDATE %s SET %s WHERE %s = "%s"',
													$this->APP->security->dbescape($this->table),
													$upd_fields,
													$this->APP->security->dbescape($where_field),
													$this->APP->security->dbescape($where_value, $this->getSecurityRule($where_field, 'allow_html')));

			}


			// Pass result to after update
			$result = $this->results();

			// run the activity log
			if($result){
				$this->activity_detect_changes('update', $where_value, $fields, $this->existing_record);
			}

			$this->after_update($result, $where_value, $where_field, $fields, $this->existing_record);

			return $result;

		}

		return false;

	}


	/**
	 *
	 * @param <type> $fields
	 */
	protected function getDefaults($fields = false){
		foreach($this->field_defaults as $field => $default){
			if(!isset($fields[$field]) || empty($fields[$field])){
				$fields[$field] = $default;
			}
		}
		$this->field_defaults = array();
		return $fields;
	}



//+-----------------------------------------------------------------------+
//| ACTIVITY FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 *
	 * @param <type> $old_values
	 * @param <type> $new_values
	 * @return <type>
	 */
	public function activity_detect_changes($type, $record_id, $new_values, $old_values = false){
		if($this->APP->isLibraryLoaded('Activity') && in_array($this->table, $this->APP->config('activity_watch_tables'))){

			// set a hash for this activity so we can group simultaneous changes together
			$key = sha1($type . $this->table .  $record_id . time());

			// if old vals is an array, we're running an update
			if($type == 'update' && is_array($old_values)){
				foreach($old_values as $old_key => $old_val){
					if(isset($new_values[$old_key])){
						if((!empty($old_val) || !empty($new_values[$old_key]))){
							if($old_val !== $new_values[$old_key]){
								$this->activity_log_change($key, $type, $this->table, $record_id, $old_key, $old_val, $new_values[$old_key]);
							}
						}
					}
				}
			}

			// if we're running an insert
			if($type == 'insert' && is_array($new_values)){
				foreach($new_values as $field => $val){
					$this->activity_log_change($key, $type, $this->table, $record_id, $field, false, $val);
				}
			}

			// if we're running an delete
			if($type == 'delete'){
				$this->activity_log_change($key, $type, $this->table, $record_id, $this->getPrimaryKey(), false, false);
			}
		}
		return false;
	}


	/**
	 *
	 * @param <type> $field
	 * @param <type> $old_value
	 * @param <type> $new_value
	 */
	public function activity_log_change($key, $type, $table, $record_id, $field, $old_value, $new_value){
		if($this->APP->isLibraryLoaded('Activity')){
			$this->APP->activity->logChange($key, $type, $table, $record_id, $field, $old_value, $new_value);
		}
	}


//+-----------------------------------------------------------------------+
//| FIELD ERROR HANDLING FUNCTIONS
//+-----------------------------------------------------------------------+


	/**
	 * Adds a new field validation error to the error queue
	 * @param string $field
	 * @param string $message
	 */
	public function addError($field, $message){

		$this->error = true;

		if(isset($this->errors[$field]) && is_array($this->errors[$field])){
			array_push($this->errors[$field], $message);
		} else {
			$this->errors[$field] = array($message);
		}
	}


	/**
	 * Returns an array of current form errors
	 * @return array
	 * @access public
	 */
	public function getErrors(){
		return $this->errors;
	}


	/**
	 * Returns a boolean whether there is a field validation error or not
	 * @return boolean
	 * @access public
	 */
	final public function error(){
		return $this->error;
	}
}
?>