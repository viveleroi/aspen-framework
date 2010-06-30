<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Stores and retrieves cached data
 * @package Aspen_Framework
 */
class Cache extends Library {
	
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
	 * Prevents the browser from caching the current output.
	 * @access public
	 */
	public function noCache() {
		header("Expires: Mon, 1 Jan 2000 08:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}
	
	
	/**
	 * Initialized the cache functions, checks dirs, etc
	 * @access public
	 */
	public function enable(){
		
		$this->dir = app()->config('cache_dir');
		
		if(!$this->checkDirectory()){
			app()->error->raise(1,
				'Caching is enabled, but directory is not writeable. Dir: ' . $this->dir, __FILE__, __LINE__);
		}
	}
	
	
	/**
	 * Checks for a valid directory, attempts to create
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
	 * Checks for a file for this current data
	 * @param string $key
	 * @param boolean $create
	 * @return boolean
	 * @access private
	 */
	private function checkFile($key, $create = true){
	
		$this->full_path = $this->dir . DS . sha1($key) . '.cache';
		
		if(!$fileexists = file_exists($this->full_path)){
			if($create){
				app()->log->write('Attempting to create cache file ' . $this->full_path);
				if(!$fileexists = touch($this->full_path)){
					$this->error->raise(1, "Failed creating cache file: " . $this->full_path, __FILE__, __LINE__);
				}
			}
		}
		
		return $fileexists;
		
	}
	
	
	/**
	 * Stores new data in the cache
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
			
			app()->log->write('Saving data to cache file. Expires: ' . $expiration);
		
			// open for writing
			app()->file->useFile($this->full_path);
			app()->file->write($metadata, 'w');
			
			return true;
			
		}
		return false;
	}
	
	
	/**
	 * Returns the stored value
	 * @param string $key
	 * @return mixed
	 * @access public
	 */
	public function getData($key){
		
		$data = false;
		
		if($this->checkFile($key, false)){
		
			// open for writing
			app()->file->useFile($this->full_path);
			$value = app()->file->read();
			
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
					app()->log->write('Cache file is expired, ignoring: ' . $this->full_path);
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