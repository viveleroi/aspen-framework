<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Validates and handles form data.
 * @package Aspen_Framework
 */
class Form extends Library {

	/**
	 * @var array $_form_fields Holds an array of form fields
	 * @access private
	 */
	private $_form_fields;

	/**
	 * @var array $_form_errors Holds an array of form field validation errors
	 * @access private
	 */
	private $_form_errors = array();

	/**
	 * Flags a validation error
	 * @var boolean $_error
	 * @access private
	 */
	private $_error = false;
	
	/**
	 * @var string $_primary_key_field Holds the field name of our primary key
	 * @access private
	 */
	private $_primary_key_field = false;
	
	/**
	 * @var string $param_type Holds the type of superglobal we're accessing
	 * @access private
	 */
	private $param_type = 'post';
	
	/**
	 * @var string $table Holds the db table we're using, if any
	 * @access private
	 */
	private $table = false;
	
	
	/**
	 * Loads a single record - field names and values
	 * @param string $table
	 * @param integer $id
	 * @param string $field
	 * @access public
	 */
	public function load($table, $id = false, $field = false){
		if($id){
			define('ADD_OR_EDIT', 'edit');
			define('IS_EDIT_PAGE', true);
			$this->loadRecord($table, $id, $field);
		} else {
			define('ADD_OR_EDIT', 'add');
			define('IS_EDIT_PAGE', false);
			$this->loadTable($table);
		}
	}


	/**
	 * Loads a table's fields and it's schema
	 * @param string $table
	 * @access private
	 */
	private function loadTable($table = false){
	
		$this->table = $table;

		if($this->table){

			$model = $this->APP->model->open($this->table);
			$this->_primary_key_field = $model->getPrimaryKey();

			foreach($model->getSchema() as $field){

				$default_val = $field->has_default ? $field->default_value : '';
				$this->APP->form->addField($field->name, $default_val, $default_val);

			}
		}
	}


	/**
	 * Loads a single record - field names and values
	 * @param string $table
	 * @param integer $id
	 * @param string $field
	 * @access private
	 */
	private function loadRecord($table, $id = false, $field = false){

		$this->table = $table;
	
		if($id){
			
			$model = $this->APP->model->open($this->table);
			$this->_primary_key_field = $model->getPrimaryKey();
		
			$field = $field ? $field : $this->_primary_key_field;
			
			$model->select();
			$model->where($field, $this->APP->security->dbescape($id));
			$records = $model->results();

			if($records['RECORDS']){
				foreach($records['RECORDS'] as $record){
					foreach($record as $field => $value){
						$this->addField($field, $value, $value);
					}
				}
			} else {
				$this->loadTable($this->table);
			}
		} else {
			$this->loadTable($this->table);
		}
	}
	
	
	/**
	 * Initiates the insert/update queries on form data
	 * @param integer $id
	 * @return integer
	 * @access public
	 */
	public function save($id = false){
		
		$model 		= $this->APP->model->open($this->table);
		$success 	= false;
		
		// build the array of field/vars
		$fields = array();
		foreach($model->getSchema() as $field){
			if(!$field->primary_key){
				$fields[$field->name] = $this->APP->form->cv($field->name, false);
			}
		}
		
		// If there are no form errors, then attempt to process the database action.
		// The database action will force the model validation and will return false if
		// something fails.
		// If a form error does exist, then we proceed with finding any other
		// field validation errors from the model, but without actually saving the data.
		if(!$this->error()){
			$success = $id ? $model->update($fields, $id) : $model->insert($fields);
		} else {
			$model->validate($fields, $id);
		}
		
		// if failed, pull all model validation errors into form array
		if(!$success){
			foreach($model->getErrors() as $field => $errors){
				foreach($errors as $error){
					$this->addError($field, $error);
				}
			}
		}
		
		return $success;
		
	}

	
	/**
	 * Determines whether or not a form has been submitted
	 * @param string $method
	 * @param strong $field
	 * @return boolean
	 * @access public
	 */
	public function isSubmitted($method = 'post', $field = false){
		
		$submitted = false;
		
		// verify field isset - we dont care about the value
		$data = $this->APP->params->getRawSource($method);
		if(is_array($data) && count($data)){
			if($field){
				$submitted = $this->APP->params->{$method}->keyExists($field);
			} else {
				$submitted = true;
			}
		}
		
		if($submitted){
			if($submitted){
				if($method == 'post'){
					$this->loadPOST();
				}
				if($method == 'get'){
					$this->loadGET();
				}
			}
		
			// if token authorization is enabled, we must authenticate
			if($this->APP->config('require_form_token_auth')){
				
				$sess_token = $this->APP->params->session->getAlnum('form_token');

				if(empty($sess_token) || $sess_token != $this->APP->params->{$method}->getAlnum('token')){
					$_SESSION['form_token'] = false;
					$this->addError('token', 'An invalid token was provided. The form was not processed.');
				}
			}
		}
		
		return $submitted;
	}
	

	/**
	 * Adds a new field to our form schema
	 * @param string $name
	 * @param mixed $default_value
	 * @param mixed $current_value
	 * @access public
	 */
	public function addField($name, $default_value = '', $current_value = false){

		$this->_form_fields[$name] = array(
											'default_value' => $default_value,
											'post_value' => false,
											'current_value' => ($current_value ? $current_value : $default_value));

	}
	
	
	/**
	 * Adds each item in an array as a field
	 * @param array $fields
	 * @access public
	 */
	public function addFields($fields){
		if(is_array($fields)){
			foreach($fields as $name){
				$this->addField($name);
			}
		}
	}


	/**
	 * Sets a default value of a form field
	 * @param string $field
	 * @param mixed $value
	 * @access public
	 */
	public function setDefaultValue($field = false, $value = false){
		if($field){
			$this->_form_fields[$field]['default_value'] = $value;
			$this->_form_fields[$field]['current_value'] = $value;
		}
	}
	
	
	/**
	 * Returns the default value for a field
	 * @param string $field
	 * @param boolean $escape
	 * @return mixed
	 * @access public
	 */
	public function getDefaultValue($field, $escape = true){

		$value = false;

		if(isset($this->_form_fields[$field])){
			$value = $this->_form_fields[$field]['default_value'];
			$value = $escape ? $this->APP->security->dbescape($value, $this->APP->model->getSecurityRule($field, 'allow_html')) : $value;
		}

		return $value;

	}
	
	
	/**
	 * Resets all form fields to their default values
	 * @access public
	 */
	public function resetDefaults(){
		if(is_array($this->_form_fields)){
			foreach($this->_form_fields as $name => $value){
				$this->_form_fields[$name]['current_value'] = $this->_form_fields[$name]['default_value'];
			}
		}
	}


	/**
	 * Sets the current value of a form field
	 * @param string $field
	 * @param mixed $value
	 * @access public
	 */
	public function setCurrentValue($field = false, $value = false){
		if($field){
			$this->_form_fields[$field]['post_value'] = $value;
			$this->_form_fields[$field]['current_value'] = $value;
		}
	}

	
	/**
	 * Returns the current value for a field
	 * @param string $field
	 * @param boolean $escape
	 * @return mixed
	 * @access public
	 */
	public function cv($field, $escape = false){
		return $this->getCurrentValue($field, $escape);
	}
	
	
	/**
	 * Returns the current value for a field
	 * @param string $field
	 * @param boolean $escape
	 * @return mixed
	 * @access public
	 */
	public function getCurrentValue($field, $escape = false){

		$value = false;

		if(isset($this->_form_fields[$field])){
			$value = $this->_form_fields[$field]['current_value'];
		}

		$value = $escape ? $this->APP->security->dbescape($value, $this->APP->model->getSecurityRule($field, 'allow_html')) : $value;

		return $value;

	}
	

	/**
	 * Returns an array of all fields and their current values
	 * @return array
	 * @access public
	 */
	public function getCurrentValues(){

		$current_values = array();

		if(is_array($this->_form_fields)){
			foreach($this->_form_fields as $field => $bits){
				$current_values[$field] = $this->cv($field);
			}
		}

		return $current_values;

	}



	/**
	 * Imports all values for current fields from POST data
	 * @access private
	 */
	public function loadPOST(){
		$this->loadIncomingValues('post');
	}


	/**
	 * Imports all values for current fields from GET data
	 * @access private
	 */
	public function loadGET(){
		$this->loadIncomingValues('get');
	}
	
	
	/**
	 * Imports all values for current fields from incoming GET/POST data
	 * @access private
	 */
	public function loadIncomingValues($method = 'post'){
		
		$this->param_type 	= $method;
		$field_model 		= false;
		$schema 			= $this->APP->model->getSchema();

		if(is_array($this->_form_fields)){
			foreach($this->_form_fields as $field => $bits){

				// identify field from schema
				$field_model = false;
				if(isset($schema[strtoupper($field)])){
					$field_model = $schema[strtoupper($field)];
				}
				
				// determine security method
				$param_access_type	= 'getRaw';
				if(is_object($field_model) && isset($field_model->type)){
					if(in_array($field_model->type, $this->APP->config('mysql_field_group_dec'))){
						$param_access_type = 'getFloat';
					}
					elseif(in_array($field_model->type, $this->APP->config('mysql_field_group_int'))){
						$param_access_type = 'getDigits';
					}
				}
				
				// get core array, so we can verify if it's even set
				$source = $this->APP->params->getRawSource($this->param_type);
				$get_val = $this->APP->params->{$this->param_type}->{$param_access_type}($field);

				// if array key not set, we use the current value
				if(!array_key_exists($field, $source)){
					$this->_form_fields[$field]['current_value'] = $this->_form_fields[$field]['default_value'];
				} else {
	
					// if array key set and a primary field, set post and current to default
					if($field == $this->_primary_key_field){
						$this->_form_fields[$field]['post_value'] = $this->_form_fields[$field]['default_value'];
						$this->_form_fields[$field]['current_value'] = $this->_form_fields[$field]['post_value'];
						
					// otherwise, set value to incoming
					} else {
						$this->_form_fields[$field]['post_value'] = $get_val;
						$this->_form_fields[$field]['current_value'] = $this->_form_fields[$field]['post_value'];
					}
				}
			}
		}
	}
	
	
	/**
	 * Loads in values straight from get/post from an array of field names
	 * @return array
	 * @access public
	 */
	public function loadSingleValues($param_type = 'get', $fields){
       
        $values = array();
       
        if(is_array($fields)){
            foreach($fields as $field){
                $values[$field] = $this->APP->params->{$param_type}->getRaw($field);
            }
        }

        return $values;
       
    }
	
	
	/**
	 * Triggers a validation error message
	 * @param string $field
	 * @param string $message
	 * @access public
	 */
	public function addError($field, $message){

		$this->_error = true;

		if(isset($this->_form_errors[$field]) && is_array($this->_form_errors[$field])){
			array_push($this->_form_errors[$field], $message);
		} else {
			$this->_form_errors[$field] = array($message);
		}
	}


	/**
	 * Returns an array of current form errors
	 * @return array
	 * @access public
	 */
	public function getErrors($custom_sort = false){

		// if no custom sort provided, we need to sort the errors
		// according to their schema position
		if(!$custom_sort && $this->APP->isInstalled() && $this->table){
			$custom_sort = array();
			$model = $this->APP->model->open($this->table);
			foreach($model->getSchema() as $field){
				$custom_sort[$field->name] = false;
			}
		} else {
			// currently, custom sort defined as vals not keys so we must flip
			if(is_array($custom_sort)){
				$custom_sort = array_flip($custom_sort);
			}
		}

		if(is_array($custom_sort)){
			$diff = array_diff_key($this->_form_errors, $custom_sort);
			$custom_sort = array_merge($custom_sort, $diff);

			// sort errors for known fields
			$final_errors = array();
			foreach($custom_sort as $field => $val){
				$errors = $this->getFieldErrors($field);
				if($errors){
					$final_errors[$field] = $errors;
				}
			}
		} else {
			$final_errors = $this->_form_errors;
		}

		return $final_errors;

	}


	/**
	 * Returns an array of errors for a specific field
	 * @param string $field
	 * @return mixed
	 */
	public function getFieldErrors($field = false){
		if($field && array_key_exists($field, $this->_form_errors)){
			return $this->_form_errors[$field];
		}
		return false;
	}
	
	
	/**
	 * Prints out form error messages using html wrapping defined in config
	 * @access public
	 */
	public function printErrors($custom_sort = false){

		$lines = '';

		if($this->error()){
			foreach($this->APP->form->getErrors($custom_sort) as $errors){
				foreach($errors as $field => $error){
					$lines .= sprintf($this->APP->config('form_error_line_html'), $error);
				}
			}
		
			print sprintf($this->APP->config('form_error_wrapping_html'), $lines);
		}
	}


	/**
	 * Returns a boolean whether there is an error or not
	 * @return boolean
	 * @access public
	 */
	public function error(){
		return $this->_error;
	}



//+-----------------------------------------------------------------------+
//| VALIDATION FUNCTIONS
//+-----------------------------------------------------------------------+

	/**
	 * Checks a form field for content
	 * @param string $field
	 * @return boolean
	 * @access public
	 */
	public function isFilled($field){
		$value = $this->cv($field);
		return !empty($value);
	}


	/**
	 * Compares two field values
	 * @param string $field_1
	 * @param string $field_2
	 * @return boolean
	 * @access public
	 */
	public function fieldsMatch($field_1, $field_2){
		return $this->cv($field_1) == $this->cv($field_2);
	}
}
?>