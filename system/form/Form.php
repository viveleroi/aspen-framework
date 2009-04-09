<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Validates and handles form data.
 * @package Aspen_Framework
 */
class Form {

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
	 * @abstract Flags a validation error
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
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Contrucor, obtains an instance of the original app
	 * @return Form_validator
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance(); }


	/**
	 * @abstract Loads a table's fields and it's schema
	 * @param string $table
	 * @access public
	 */
	public function loadTable($table = false){
	
		$this->table = $table;

		if($this->table){

			$this->APP->model->openTable($this->table);
			$this->_primary_key_field = $this->APP->model->getPrimaryKey();

			foreach($this->APP->model->getSchema() as $field){

				$default_val = $field->has_default ? $field->default_value : '';
				$this->APP->form->addField($field->name, $default_val, $default_val);

			}
		}
	}


	/**
	 * @abstract Loads a single record - field names and values
	 * @param string $table
	 * @param integer $id
	 * @param string $field
	 * @access public
	 */
	public function loadRecord($table, $id = false, $field = false){

		$this->table = $table;
	
		if($id){
			
			$this->APP->model->openTable($this->table);
			$this->_primary_key_field = $this->APP->model->getPrimaryKey();
		
			$field = $field ? $field : $this->_primary_key_field;
			
			$this->APP->model->select($this->table);
			$this->APP->model->where($field, $this->APP->security->dbescape($id));
			$records = $this->APP->model->results();

			if($records['RECORDS']){
				foreach($records['RECORDS'] as $record){
					foreach($record as $field => $value){
						$this->APP->form->addField($field, $value, $value);
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
	 * @abstract Initiates the insert/update queries on form data
	 * @param integer $id
	 * @return integer
	 * @access public
	 */
	public function save($id = false){
		if(!$this->error()){
			if($id){
				return $this->APP->model->updateForm($this->table, $id);
			} else {
				return $this->APP->model->insertForm($this->table);
			}
		} else {
			return false;
		}
	}

	
	/**
	 * @abstract Determines whether or not a form has been submitted
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
	 * @abstract Adds a new field to our form schema
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
	 * @abstract Adds each item in an array as a field
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
	 * @abstract Sets a default value of a form field
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
	 * @abstract Returns the default value for a field
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
	 * @abstract Resets all form fields to their default values
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
	 * @abstract Sets the current value of a form field
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
	 * @abstract Returns the current value for a field
	 * @param string $field
	 * @param boolean $escape
	 * @return mixed
	 * @access public
	 */
	public function cv($field, $escape = false){
		return $this->getCurrentValue($field, $escape);
	}
	
	
	/**
	 * @abstract Returns the current value for a field
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
	 * @abstract Returns an array of all fields and their current values
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
	 * @abstract Imports all values for current fields from POST data
	 * @access private
	 */
	public function loadPOST(){
		$this->loadIncomingValues('post');
	}


	/**
	 * @abstract Imports all values for current fields from GET data
	 * @access private
	 */
	public function loadGET(){
		$this->loadIncomingValues('get');
	}
	
	
	/**
	 * @abstract Imports all values for current fields from incoming GET/POST data
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
	 * @abstract Loads in values straight from get/post from an array of field names
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
	 * @abstract Triggers a validation error message
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
	 * @abstract Returns an array of current form errors
	 * @return array
	 * @access public
	 */
	public function getErrors(){
		return $this->_form_errors;
	}
	
	
	/**
	 * @abstract Prints out form error messages using html wrapping defined in config
	 * @access public
	 */
	public function printErrors(){

		$lines = '';

		if($this->error()){
			foreach($this->APP->form->getErrors() as $errors){
				foreach($errors as $field => $error){
					$lines .= sprintf($this->APP->config('form_error_line_html'), $error);
				}
			}
		
			print sprintf($this->APP->config('form_error_wrapping_html'), $lines);
		}
	}


	/**
	 * @abstract Returns a boolean whether there is an error or not
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
	 * @abstract Checks a form field for content
	 * @param string $field
	 * @return boolean
	 * @access public
	 */
	public function isFilled($field){
		$value = $this->cv($field);
		return !empty($value);
	}


	/**
	 * @abstract Compares two field values
	 * @param string $field_1
	 * @param string $field_2
	 * @return boolean
	 * @access public
	 */
	public function fieldsMatch($field_1, $field_2){
		return $this->cv($field_1) == $this->cv($field_2);
	}
	
	
	/**
	 * @abstract Checks for an integer
	 * @param string $field
	 * @return boolean
	 * @access public
	 */
	public function isInt($field){
		return $this->APP->params->{$this->param_type}->testInt($field);
	}
	
	
	/**
	 * @abstract Checks for a float value
	 * @param string $field
	 * @return boolean
	 * @access public
	 */
	public function isFloat($field){
		return $this->APP->params->{$this->param_type}->testFloat($field);
	}
	
	
	/**
	 * @abstract Checks for an alphanumeric string
	 * @param string $field
	 * @return boolean
	 * @access public
	 */
	public function isAlnum($field){
		return $this->APP->params->{$this->param_type}->testAlnum($field);
	}

	
	/**
	 * @abstract Checks for an alpha string
	 * @param string $field
	 * @return boolean
	 * @access public
	 */
	public function isAlpha($field){
		return $this->APP->params->{$this->param_type}->testAlpha($field);
	}
	
	
	/**
	 * @abstract Checks for an IP address
	 * @param string $field
	 * @return boolean
	 * @access public
	 */
	public function isIp($field){
		return $this->APP->params->{$this->param_type}->testIp($field);
	}
	

	/**
	 * @abstract Checks whether or not the string is a credit card number
	 * @param string $field
	 * @param string $type
	 * @return boolean
	 * @access public
	 */
	public function isCreditCard($field, $type){
		return $this->APP->params->{$this->param_type}->testCcnum($field, $type);
	}
	
	
	/**
	 * @abstract Checks whether or not the string is a valid date
	 * @param string $field
	 * @param string $type
	 * @return boolean
	 * @access public
	 */
	public function isDate($field){
		return $this->APP->params->{$this->param_type}->testDate($field);
	}


	/**
	 * @abstract Checks for a valid email format
	 * @param string $field
	 * @return boolean
	 * @access public
	 */
	public function isEmail($field){
		return $this->APP->params->{$this->param_type}->testEmail($field);
	}

	
	/**
	 * @abstract Checks for a valid float/digit
	 * @param feild $field
	 * @return boolean
	 * @access public
	 */
	public function isCurrency($field){
		$cleaned_var = str_replace(array('$', ',', '.'), "", $this->cv($field));
		return ctype_digit($cleaned_var);
	}
	

	/**
	 * @abstract Checks for a valid phone number
	 * @param string $phonenumber
	 * @param string $country
	 * @return boolean
	 * @access public
	 */
	public function isPhoneNumber($field, $country = 'US'){
		return $this->APP->params->{$this->param_type}->testPhone($field, $country);
	}
}
?>