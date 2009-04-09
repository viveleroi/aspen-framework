<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Provides a method of writing to a log file.
 * @package Aspen_Framework
 */
class Log {
	
	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;
	
	/**
	 * @var boolean $on Whether or not logging  is enabled
	 * @access private
	 */
	private $on = false;
	
	/**
	 * @var boolean $dir The directory path to our log files
	 * @access private
	 */
	private $dir = false;
	
	/**
	 * @var boolean $full_path Contains the full path to our current log file
	 * @access private
	 */
	private $full_path = false;

	
	/**
	 * @abstract Constructor
	 * @return Log
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance(); }
	
	
	/**
	 * @abstract Sets up the directories and files as necessary
	 * @access public
	 */
	public function enable(){
		
		$loaded = false;
		
		$this->on 		= $this->APP->config('enable_logging');
		$this->dir 		= $this->APP->config('log_dir');
		$this->level 	= $this->APP->config('log_verbosity');
		
		if($this->on && $this->dir){
		
			// verify directory exists and is writeable
			if(!$this->checkDirectory()){
				$this->on = false;
				$this->APP->error->raise(1,
					'Logging is enabled, but directory is not writeable. Dir: ' . $this->dir, __FILE__, __LINE__);
			}
			
			// create a log file
			if(!$this->createLogFile()){
				$this->on = false;
				$this->APP->error->raise(1,
					'Failed creating new log file.', __FILE__, __LINE__);
			}
			
			if($this->on){
				$loaded = true;
			}
		}
		
		
		if($loaded){
			
			$this->write('Logging has been activated at ' . date("Y-m-d H:i:s") . '.', 'w');
			
			if($this->level == 1){
				$this->logCoreInfo();
			}
		}
	}
	
	
	/**
	 * @abstract Checks for a valid directory, attempts to create
	 * @return boolean
	 * @access private
	 */
	private function checkDirectory(){
		
		$dir_ok = false;
		
		if($this->dir){
			if(is_dir($this->dir) && is_writeable($this->dir)){
				$dir_ok = true;
			} else {
				$dir_ok = mkdir($this->dir);
			}
		}
		
		return $dir_ok;
		
	}
		
	
	/**
	 * @abstract Uses or creates new log files
	 * @return boolean
	 * @access private
	 */
	private function createLogFile(){
		
		$new_filename = 'log';

		if($this->APP->config('timestamp_log_file')){
			$new_filename .= '-' . REQUEST_START;
		}
		
		$this->full_path = $this->dir . DS . $new_filename;
		
		if(!$fileexists = file_exists($this->full_path)){
			$fileexists = touch($this->full_path);
		}
		
		return $fileexists;
		
	}
	
	
	/**
	 * @abstract Writes a new message to the log file
	 * @param string $message
	 * @access public
	 */
	public function write($message = '(empty message)', $mode = 'a'){
		if($this->on){
			$this->APP->file->useFile($this->full_path);
			
			if(is_array($message) || is_object($message)){
				$this->APP->file->write( print_r($message, true) . "\n", $mode);
			} else {
				$this->APP->file->write($message . "\n", $mode);
			}
		}
	}
	
	
	/**
	 * @abstract Writes a breaking line
	 * @access public
	 */
	public function hr(){
		$this->write('++======================================================++');
	}
	
	/**
	 * @abstract Writes a new section header
	 * @access public
	 */
	public function section($title = 'Section'){
		$this->write('');
		$this->write('++======================================================++');
		$this->write('++  ' . $title);
		$this->write('++======================================================++');
	}
	
	
	/**
	 * @abstract Logs all core aspen framework data to the logfile
	 * @access private
	 */
	private function logCoreInfo(){
		if($this->on){
			
			// record all constants
			$this->section('Constants');
			$defines = get_defined_constants(true);
			foreach($defines['user'] as $define => $value){
				$this->write('Constant ' . $define . ' was set to a value of: ' . $value);
			}
			
			// record all configurations
			$this->section('Configurations');
			$config = $this->APP->getConfig();
			foreach($config as $config => $value){
				$this->write('Config ' . $config . ' was set to a value of: ' . $value);
			}
			
			$this->section('Loaded System Libraries');
			$lib = $this->APP->getLoadedLibraries();
			foreach($lib as $class){
				$this->write('Library Class ' . $class['classname'] . ' was loaded.');
			}
			
			$this->section('Session Data');
			$session = $this->APP->params->getRawSource('session');
			foreach($session as $key => $value){
				$this->write('$_SESSION[\''.$key.'\'] = ' . $value);
			}
			
			$this->section('POST Data');
			$post = $this->APP->params->getRawSource('post');
			foreach($post as $key => $value){
				$this->write('$_POST[\''.$key.'\'] = ' . $value);
			}
			
			$this->section('GET Data');
			$get = $this->APP->params->getRawSource('get');
			foreach($get as $key => $value){
				$this->write('$_GET[\''.$key.'\'] = ' . $value);
			}
			
			$this->section('SERVER Data');
			$server = $this->APP->params->getRawSource('server');
			foreach($server as $key => $value){
				$this->write('$_SERVER[\''.$key.'\'] = ' . $value);
			}
			
			// save all urls/paths to log for debugging
			$this->section('Router Urls & Paths');
			$this->write('Router::getDomainUrl set to: ' . $this->APP->router->getDomainUrl());
			$this->write('Router::getApplicationUrl set to: ' . $this->APP->router->getApplicationUrl());
			$this->write('Router::getPath set to: ' . $this->APP->router->getPath());
			$this->write('Router::getInterfaceUrl set to: ' . $this->APP->router->getInterfaceUrl());
			//$this->write('Router::getModuleUrl set to: ' . $this->APP->router->getModuleUrl());
			$this->write('Router::getStaticContentUrl set to: ' . $this->APP->router->getStaticContentUrl());
			$this->write('Router::getFullUrl set to: ' . $this->APP->router->getFullUrl());
			
			$this->section('Bootstrap');
			$this->write('Installed checks returned ' . ($this->APP->isInstalled() ? 'true' : 'false'));
			
			if($this->APP->checkUserConfigExists()){
				$this->write('Found user config file.');
			} else {
				$this->write('User config was NOT FOUND.');
			}
		}
	}
}
?>