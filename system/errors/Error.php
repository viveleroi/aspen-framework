<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */


/**
 * Shortcut to return an instance of our original app
 * @return object
 */
function &error(){
	return app()->error;
}


/**
 * Please note that this class relies
 * as little as possible on other classes
 * within this framework so that we
 * can avoid as many failure points as possible.
 *
 * @package Aspen_Framework
 */
class Error  {


	/**
	 * Raises a new error message
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

		while (ob_get_level()) {
			ob_end_clean();
		}

		$errType = array (
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
			2048 => "Unknown",
			8192 => "Deprecated"
		);

		$trace = array();
		$db = debug_backtrace();
		foreach($db as $file_t){
			if(isset($file_t['file'])){
				$trace[] = array('file'=>$file_t['file'],'line'=>$file_t['line'],'function'=>$file_t['function']);
			}
		}

		// determine uri
		if(is_object(app()->router) && method_exists(app()->router, 'fullUrl')){
			$uri = router()->fullUrl();
		} else {
			$uri = $this->getServerValue('REQUEST_URI');
		}

		$error = array(
				'application' => app()->config('application'),
				'version_complete' => VERSION_COMPLETE,
				'version' => VERSION,
				'build' => BUILD,
				'date' => date("Y-m-d H:i:s"),
				'gmdate' => gmdate("Y-m-d H:i:s"),
				'visitor_ip' => $this->getServerValue('REMOTE_ADDR'),
				'referrer_url' => $this->getServerValue('HTTP_REFERER'),
				'request_uri' => $uri,
				'user_agent' => $this->getServerValue('HTTP_USER_AGENT'),
				'error_type' => $errType[$errNo],
				'error_message' => $errMsg,
				'error_no' => $errNo,
				'file' => $file,
				'line' => $line,
				'trace' => (empty($trace) ? false : $trace)
			);

		// if we're going to save to a db
		if(app()->config('save_error_to_db') && app()->checkDbConnection()){

			$error_sql = sprintf('
				INSERT INTO error_log (
					application, version, date, visitor_ip, referer_url, request_uri,
					user_agent, error_type, error_file, error_line, error_message)
				VALUES ("%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s")',
					mysql_real_escape_string($error['application']),
					mysql_real_escape_string($error['version_complete']),
					mysql_real_escape_string($error['date']),
					mysql_real_escape_string($error['visitor_ip']),
					mysql_real_escape_string($error['referrer_url']),
					mysql_real_escape_string($error['request_uri']),
					mysql_real_escape_string($error['user_agent']),
					mysql_real_escape_string($error['error_no']),
					mysql_real_escape_string($error['file']),
					mysql_real_escape_string($error['line']),
					mysql_real_escape_string($error['error_message'])
				);

			if(!app()->db->Execute($error_sql)){
				print 'There was an error trying to log the most recent error to the database:<p>'
						.  app()->db->ErrorMsg()
						. '<p>Query was:</p>' . $error_sql;
						exit;
			}
		}

		// if logging exists, log this error
		if(isset(app()->log) && is_object(app()->log)){
			app()->log->write(sprintf('ERROR (File: %s/%s: %s',$error['file'],$error['line'],$error['error_message']));
		}

		// If we need to display this error, do so
		if($errNo <= app()->config('minimum_displayable_error')){
			if(
				!app()->env->keyExists('SSH_CLIENT')
				&& !app()->env->keyExists('TERM')
				&& !app()->env->keyExists('SSH_CONNECTION')
				&& app()->server->keyExists('HTTP_HOST')){

				template()->resetTemplateQueue();
				template()->addView(template()->getTemplateDir().DS . 'error.tpl.php');
				template()->display(array('error'=>$error));
				exit;

			}
		}

		// post the errors to json-enabled api (snowy evening)
		if(app()->config('error_json_post_url')){

			$params = array(
					'api_key'=>app()->config('error_json_post_api_key'),
					'project_id'=>app()->config('error_json_post_proj_id'),
					'payload'=>json_encode($error));

			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,app()->config('error_json_post_url'));
			curl_setopt($ch,CURLOPT_POST,count($error));
			curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($params));
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			$result = curl_exec($ch);
			curl_close($ch);
		}
	}

	
	/**
	 *
	 * @param <type> $exception
	 */
	public function raiseException($exception){
		$this->raise(2, $exception->getMessage(), $exception->getFile(), $exception->getLine());
	}


	/**
	 * Returns a server value, uses params class if loaded
	 * @param string $key
	 * @param string $default
	 * @return string
	 * @access private
	 */
	private function getServerValue($key, $default = 'N/A'){
		if(isset(app()->params) && is_object(app()->params)){
			return app()->server->getRaw($key);
		} else {
			return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
		}
	}
}
?>