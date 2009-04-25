<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Stores and retrieves cached data
 * @package Aspen_Framework
 */
class Cache {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	protected $APP;
	
	/**
	 * @var boolean $dir The directory path to our cache files
	 * @access private
	 */
	private $dir;
	
	/**
	 * @var boolean $full_path Contains the full path to our current log file
	 * @access private
	 */
	private $full_path;


	/**
	 * @abstract Contrucor, obtains an instance of the original app
	 * @return Form_validator
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance(); }
	
	
	/**
	 * @abstract Initialized the cache functions, checks dirs, etc
	 * @access public
	 */
	public function enable(){
		
		$this->dir = $this->APP->config('cache_dir');
		
		if(!$this->checkDirectory()){
			$this->APP->error->raise(1,
				'Caching is enabled, but directory is not writeable. Dir: ' . $this->dir, __FILE__, __LINE__);
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
				$dir_ok = @mkdir($this->dir);
			}
		}
		
		return $dir_ok;
		
	}
	
	
	/**
	 * @abstract Checks for a file for this current data
	 * @param string $key
	 * @param boolean $create
	 * @return boolean
	 * @access private
	 */
	private function checkFile($key, $create = true){
	
		$this->full_path = $this->dir . DS . sha1($key) . '.cache';
		
		if(!$fileexists = file_exists($this->full_path)){
			if($create){
				$this->APP->log->write('Attempting to create cache file ' . $this->full_path);
				if(!$fileexists = touch($this->full_path)){
					$this->error->raise(1, "Failed creating cache file: " . $this->full_path, __FILE__, __LINE__);
				}
			}
		}
		
		return $fileexists;
		
	}
	
	
	/**
	 * @abstract Stores new data in the cache
	 * @param string $key
	 * @param mixed $data
	 * @param string $expiration
	 * @return boolean
	 * @access public
	 */
	public function put($key, $data = false, $expiration = false){
		
		if($this->checkFile($key)){
			
			// determine the datatype
			$datatype = gettype($data);
			
			if($datatype == 'array' || $datatype == 'object'){
				$data = serialize($data);
			}

			$metadata = '';
			$metadata .= sprintf("|Timestamp:%s\n", date("Y-m-d H:i:s"));
			$metadata .= sprintf("|Expiration:%s\n", $expiration);
			$metadata .= sprintf("|Datatype:%s\n", $datatype);
			$metadata .= sprintf("|Data:%s", $data);
			
			$this->APP->log->write('Saving data to cache file. Expires: ' . $expiration);
		
			// open for writing
			$this->APP->file->useFile($this->full_path);
			$this->APP->file->write($metadata, 'w');
			
			return true;
			
		}
		return false;
	}
	
	
	/**
	 * @abstract Returns the stored value
	 * @param string $key
	 * @return mixed
	 * @access public
	 */
	public function getData($key){
		
		$data = false;
		
		if($this->checkFile($key, false)){
		
			// open for writing
			$this->APP->file->useFile($this->full_path);
			$value = $this->APP->file->read();
			
			$matches = array();
			
			// pull timestamp
			preg_match( "|Timestamp:(.*)|i", $value, $matches);
			//$timestamp = isset($matches[1]) ? $matches[1] : false;
			
			// pull expiration
			preg_match( "|Expiration:(.*)|i", $value, $matches);
			$expiration = isset($matches[1]) ? $matches[1] : false;
			
			// pull datatype
			preg_match( "|Datatype:(.*)|i", $value, $matches);
			$datatype = isset($matches[1]) ? $matches[1] : false;
			
			// pull data
			preg_match( "|Data:(.*)|ims", $value, $matches);
			$data = isset($matches[1]) ? $matches[1] : false;
			
			// check expiration of data
			if(!empty($expiration)){
				if(strtotime($expiration) < time()){
					$this->APP->log->write('Cache file is expired, ignoring: ' . $this->full_path);
					$data = false;
				}
			}
			
			
			// return data to original datatype
			if($datatype == 'array' || $datatype == 'object'){
				$data = unserialize($data);
			}
			
		}
		
		return $data;
		
	}
}
?>