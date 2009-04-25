<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Error handling class, based off of the ErrorHandler script 2.0.1
 * from http://gosu.pl/software/mygosulib.html. Heavily modified.
 *
 * Please note that this class relies
 * as little as possible on other classes
 * within this framework so that we
 * can avoid as many failure points as possible.
 *
 * @package Aspen_Framework
 * @author Cezary Tomczak
 */
class Error {

	/**
	 * @var integer $errNo Error number
	 * @access private
	 */
	private $errNo;

	/**
	 * @var string $errMsg Error message
	 * @access private
	 */
	private $errMsg;

	/**
	 * @var string $file Source file name/path
	 * @access private
	 */
	private $file;

	/**
	 * @var integer $line Error source line number
	 * @access private
	 */
	private $line;

	/**
	 * @var array $errType Error types
	 * @access private
	 */
	private $errType;

	/**
	 * @var mixed $info
	 * @access private
	 */
	private $info;

	/**
	 * @var array $trace Trace of errors
	 * @access private
	 */
	private $trace;

	/**
	 * @var object $APP Holds an instance of our app
	 * @access private
	 */
	protected $APP;


	/**
	 * @abstract Handles our error logging/display
	 * @return ErrorHandler
	 */
	public function __construct(){ $this->APP = get_instance(); }
	
	
	/**
	 * @abstract Returns the error number
	 * @return string
	 * @access public
	 */
	public function getErrorNo(){
		return $this->errNo;
	}
	
	
	/**
	 * @abstract Returns the error message
	 * @return string
	 * @access public
	 */
	public function getErrorMessage(){
		return $this->errMsg;
	}
	
	
	/**
	 * @abstract Returns the error type
	 * @return string
	 * @access public
	 */
	public function getErrorType(){
		return $this->errType[$this->errNo];
	}

	
	/**
	 * @abstract Returns the error line
	 * @return string
	 * @access public
	 */
	public function getErrorLine(){
		return $this->line;
	}
	
	
	/**
	 * @abstract Returns the error file
	 * @return string
	 * @access public
	 */
	public function getErrorFile(){
		return $this->file;
	}
	
	
	/**
	 * @abstract Returns the error information array
	 * @return array
	 * @access public
	 */
	public function getErrorInfo(){
		return $this->info;
	}
	
	
	/**
	 * @abstract Returns the error trace
	 * @return array
	 * @access public
	 */
	public function getErrorTrace(){
		return $this->trace;
	}
	
	
	/**
	 * @abstract Raises a new error message
	 * @param integer $errNo
	 * @param string $errMsg
	 * @param string $file
	 * @param integer $line
	 * @return void
	 * @access public
	 */
	public function raise($errNo = false, $errMsg = 'An unidentified error occurred.', $file = false, $line = false) {

		// die if no errornum
		if (!$errNo) { return; }

		$this->errNo = $errNo;
		$this->errMsg = $errMsg;
		$this->file = $file;
		$this->line = $line;

		while (ob_get_level()) {
			ob_end_clean();
		}

		$this->errType = array (
			1    => "PHP Error",
			2    => "PHP Warning",
			4    => "PHP Parse Error",
			8    => "PHP Notice",
			16   => "PHP Core Error",
			32   => "PHP Core Warning",
			64   => "PHP Compile Error",
			128  => "PHP Compile Warning",
			256  => "PHP User Error",
			512  => "PHP User Warning",
			1024 => "PHP User Notice",
			2048 => "Unknown"
		);

		$this->info = array();

		if (($this->errNo & E_USER_ERROR) && is_array($arr = @unserialize($this->errMsg))) {
			foreach ($arr as $k => $v) {
				$this->info[$k] = $v;
		  	}
		}

		$this->trace = array();

		if (function_exists('debug_backtrace')) {
			$this->trace = debug_backtrace();
		 	array_shift($this->trace);
		}

		// if we're going to save to a db
		if($this->APP->config('save_error_to_db') && $this->APP->checkDbConnection()){

			$error_sql = sprintf('
				INSERT INTO error_log (
					application, version, date, visitor_ip, referer_url, request_uri,
					user_agent, error_type, error_file, error_line, error_message)
				VALUES ("%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s")',
					mysql_real_escape_string($this->APP->config('application_name')),
					VERSION . ' Framework Rev:' . FRAMEWORK_REV,
					date("Y-m-d H:i:s"),
					$this->getServerValue('REMOTE_ADDR'),
					$this->getServerValue('HTTP_REFERER'),
					$this->getServerValue('REQUEST_URI'),
					$this->getServerValue('HTTP_USER_AGENT'),
					$this->errType[$this->errNo],
					$this->file,
					$this->line,
					mysql_real_escape_string($this->errMsg)
				);

			if(!$this->APP->db->Execute($error_sql)){
				print 'There was an error trying to log the most recent error to the database:<p>'
						.  $this->APP->db->ErrorMsg()
						. '<p>Query was:</p>' . $error_sql;
						exit;
			}
		}


		// If we're emailing it to the developer
		if($this->APP->config('send_error_emails') && $this->APP->config('error_email_recipient')){

			$this->APP->mail->AddAddress($this->APP->config('error_email_recipient'));
			$this->APP->mail->From      = $this->APP->config('error_email_sender');
			$this->APP->mail->FromName  = $this->APP->config('error_email_sender_name');
			$this->APP->mail->Mailer    = "mail";

			$errorBody = VERSION . "
			DATE: " . date("Y-m-d h:i:s") . "
			VISITOR IP: " . $this->getServerValue('REMOTE_ADDR') . "
			REFERRER URL: " . $this->getServerValue('HTTP_REFERER') . "
			REQUEST URI: " . $this->APP->router->getFullUrl() . $this->getServerValue('REQUEST_URI', '') . "
			USER AGENT: " . $this->getServerValue('HTTP_USER_AGENT') . "
			ERROR TYPE: " . $this->errType[$this->errNo] . "\r";

			if (is_array($this->trace)){
				foreach ($this->trace as $k => $v){

					$errorBody .= "FILE: " . $v['file'] . "\rLINE: " . $v['line'] . "\r";

				}
			} else {
				$errorBody .= "FILE: " . $this->file . "\rLINE: " . $this->line . "\r";
			}

			$errorBody .= "
			ERROR MESSAGE:\r\r";

			if (is_array($this->info)) {
				foreach ($this->info as $k => $v) {
				  $errorBody .= "$k: $v\r";
				}
			} else {
				$errorBody .= "$this->errMsg\r";
			}

			$this->APP->mail->Subject = "Application Error: " . $this->errMsg;
			$this->APP->mail->Body = $errorBody;
			$this->APP->mail->Send();
			$this->APP->mail->ClearAddresses();

		}
		
		// if logging exists, log this error
		if(isset($this->APP->log) && is_object($this->APP->log)){
			$this->APP->log->write(sprintf('ERROR (File: %s/%s: %s', $this->file, $this->line, $this->errMsg));
		}

		// If we need to display this error, do so
		if($this->errNo <= $this->APP->config('minimum_displayable_error')){
			
			if($this->errNo > 1){
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'error.tpl.php');
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
				$this->APP->template->display();
				exit;
			} else {
				$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'error.tpl.php');
				$this->APP->template->display();
				exit;
			}
		}
	}
	
	
	/**
	 * @abstract Returns a server value, uses params class if loaded
	 * @param string $key
	 * @param string $default
	 * @return string
	 * @access private
	 */
	private function getServerValue($key, $default = 'N/A'){
		
		if(isset($this->APP->params) && is_object($this->APP->params)){
			return $this->APP->params->server->getRaw($key);
		} else {
			return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
		}
	}
}
?>