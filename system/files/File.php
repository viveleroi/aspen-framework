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
function &files(){
	return app()->files;
}


/**
 * File/directory handling class loosely based off of original concept by Antoine Bouet.
 * @package Aspen_Framework
 * @author Antoine Bouet
 */
class File  {

	/**
	 * @var string Absolute path to file
	 * @access private
	 */
	private $file_path;

	/**
	 * @var string File size in Bytes
	 * @access private
	 */
	private $file_size;

	/**
	 * @var string File owner
	 * @access private
	 */
	private $file_owner;

	/**
	 * @var string File group
	 * @access private
	 */
	private $file_group;
	
	/**
	 * @var string File permissions
	 * @access private
	 */
	private $file_permissions = 0777;

	/**
	 * @var string File extension
	 * @access private
	 */
	private $file_extension;
	
	/**
	 * @var string Mime type ( for this version are based on Apache 1.3.27 )
	 * @access private
	 */
	private $file_type;
	
	/**
	 * @var string Folder permissions
	 * @access private
	 */
	private $folder_permissions = 0777;
	
	/**
	 * @var string Used as the temp file identifier.
	 * @access private
	 */
	private $tmp_name;
	
	/**
	 * @var string $upload_directory Holds the server path of the upload directory
	 * @access private
	 */
	private $upload_directory = false;

	/**
	 * @var string $browser_url Holds the web address to the uploaded file
	 * @access private
	 */
	private $browser_url;
	

//+-----------------------------------------------------------------------+
//| DIRECTORY OPERATION FUNCTIONS
//+-----------------------------------------------------------------------+

	
	/**
	 * List files found in a directory
	 * @param string $directory
	 * @return array
	 * @access public
	 */
	public function dirList($directory){

		// create an array to hold directory list
		$results = array();
		
		if(is_dir($directory)){

			// create a handler for the directory
			$handler = opendir($directory);
	
			// keep going until all files in directory have been read
			while ($file = readdir($handler)) {
	
				// if $file isn't this directory or its parent,
				// add it to the results array
				if ($file != '.' && $file != '..'){
					$results[] = $file;
				}
			}
	
			closedir($handler);
			
		}

		return $results;

	}
	
	
	/**
	 * Creates a new directory
	 * @param string $path
	 * @return boolean
	 * @access public
	 */
	public function createDirectory($path){
		$result = false;
		if(!$result = file_exists($path)){
			if(!$result = @mkdir($path, $this->folder_permissions)){
				error()->raise(2, "Failed creating new directory: " . $path, __FILE__, __LINE__);
			}
		}
		return $result;
	}
	

	/**
	 * Removes a new directory
	 * @param string $path
	 * @return boolean
	 * @access public
	 */
	public function removeDirectory($path){
		$result = false;
		if(is_dir($path)){
			if(!$result = rmdir($path)){
				error()->raise(2, "Failed removing directory: " . $path, __FILE__, __LINE__);
			}
		}
		return $result;
	}
	
	
//+-----------------------------------------------------------------------+
//| FILE READ/WRITE FUNCTIONS
//+-----------------------------------------------------------------------+
	

	/**
	 * Initiates the class for use with a file
	 * @param string $filepath
	 * @access public
	 */
	public function useFile($filepath){
		$this->file_path = $filepath;
		$this->getfile_extension();
		$this->getType();
	}
	

	/**
	 * Opens file and returns contents
	 * @param string $file
	 * @return string
	 * @access public
	 */
	public function read($file = false){
		
		if($file){ $this->useFile($file); }
		
		$file = fread($fp = fopen($this->file_path, 'r'), filesize($this->file_path));
		fclose($fp);
		return $file;
	}
	
	
	/**
	 * Writes content to the file
	 * @param string $content
	 * @param string $mode File editing mode w=write, a=append
	 * @return boolean
	 * @access public
	 */
	public function write($content, $mode = 'w'){

		if(file_exists($this->file_path) && is_writable($this->file_path)){
			$fp = fopen($this->file_path,$mode);
			if(!fwrite($fp,$content)){
				error()->raise(2, "Failed writing contents to file: " . $this->file_path, __FILE__, __LINE__);
			}
			fclose($fp);
		}
	}


	/**
	 * Deletes the file with command appropriate for OS
	 * @param string $file
	 * @return boolean
	 * @access public
	 */
	public function delete($file = false){

		if($file){ $this->useFile($file); }

		if(is_dir($this->file_path)){
			$this->removeDirectory($this->file_path);
		}
		else if(file_exists($this->file_path)){
			if (substr(php_uname(), 0, 7) == "Windows") {
				$this->file_path  = str_replace( '/', '\\', $this->file_path);
				system( 'del /F "'.$this->file_path.'"', $result );
				if($result === 0){
					return true;
				} else {
					error()->raise(2, "Failed to delete file: " . $this->file_path, __FILE__, __LINE__);
				}
			} else {
				chmod( $this->file_path, 0775 );
				return unlink( $this->file_path );
			}
		}
		return false;
	}
	
	
	/**
	 * Checks the file against allowed types
	 * @return boolean
	 * @access private
	 */
	private function typeCheck(){
		return in_array(str_replace(".", '', $this->file_extension), app()->config('allowed_file_extensions'));
	}
	

	/**
	 * Initiates a download of a file
	 * @access public
	 */
	public function download(){
		header( "Content-type: ".$this->file_type );
		header( "Content-Length: ".$this->file_size );
		header( "Content-Disposition: attachment; filename=" . $this->file_path );
		header( "Content-Description: Data Download" );
		print file_get_contents($this->file_path);
	}
	

//+-----------------------------------------------------------------------+
//| FILE UPLOAD FUNCTIONS
//+-----------------------------------------------------------------------+

	
	/**
	 * Uploads a file to $config['upload_server_path'] from $_FILES array
	 * @param string $form_field Name of form FILE input field
	 * @param boolean $overwrite Allow overwriting existing files
	 * @param boolean $timestamp Whether or not to timestamp uploaded file
	 * @param string $rename New name for upload file
	 * @return array
	 * @access public
	 */
	public function upload($form_field = false, $overwrite = false, $timestamp = true, $rename = false){
		if(app()->config('enable_uploads')){
			if($this->setUploadDirectory() && app()->files->isArray($form_field)){
				return $this->upload_files($form_field, $rename, $overwrite, $timestamp);
			}
		} else {
			error()->raise(2, "Upload function called yet file uploads are disabled.", __FILE__, __LINE__);
		}
		return false;
	}


	/**
	 * Sets and configures the upload directory
	 * @return boolean
	 * @access private
	 */
	public function setUploadDirectory(){
		
		$this->upload_directory = app()->config('upload_server_path');
		$this->browser_url = router()->uploadsUrl();

		// check the status of the folder
		if(!is_dir($this->upload_directory)){
			if(!mkdir($this->upload_directory, 0777)){
				error()->raise(2, "Upload directory creation failed. " . $this->upload_directory, __FILE__, __LINE__);
			} else {
				return true;
			}
		} else {
			if(is_writeable($this->upload_directory)){
				return true;
			} else {
				error()->raise(2, "Could not write to upload directory." . $this->upload_directory, __FILE__, __LINE__);
			}
		}
		return false;
	}
	
	
	/**
	 * Uploads an array of / or single files
	 * @param string $form_field Name of form FILE input field
	 * @param string $rename New name for upload file
	 * @param boolean $overwrite Allow overwriting existing files
	 * @param boolean $timestamp Whether or not to timestamp uploaded file
	 * @access private
	 */
	private function upload_files($form_field = false, $rename = false, $overwrite = false, $timestamp = false){
		
		$uploads = array();

		// if form field set
		if($form_field && app()->files->isArray($form_field)){
			
			$file = app()->files->getArray($form_field);
			
			if(isset($file['name'])){
				// If the file uploads is an array
				if(is_array($file['name'])){
					
					for($cnt = 0; $cnt <= count($file['name']); $cnt++){
						
						if(!empty($file['name'][$cnt])){
						
							// constuct file array
							$file_arr['name'] 		= $file['name'][$cnt];
							$file_arr['type'] 		= $file['type'][$cnt];
							$file_arr['tmp_name'] 	= $file['tmp_name'][$cnt];
							$file_arr['error'] 		= $file['error'][$cnt];
							$file_arr['size'] 		= $file['size'][$cnt];
							
							$uploads[] = $this->upload_file($file_arr, $rename, $overwrite, $timestamp);
							
						}
					}
					
				} else {
					$uploads[] = $this->upload_file($file, $rename, $overwrite, $timestamp);
				}
			} else {
				error()->raise(2, "File name was missing from FILES array.", __FILE__, __LINE__);
			}
		} else {
			error()->raise(2, "Form field was not present in FILES superglobal.", __FILE__, __LINE__);
		}
		
		return $uploads;
		
	}


	/**
	 * Uploads a file
	 * @param string $file An array of the file information
	 * @param string $rename New name for upload file
	 * @param boolean $overwrite Allow overwriting existing files
	 * @param boolean $timestamp Whether or not to timestamp uploaded file
	 * @access private
	 */
	private function upload_file($file = false, $rename = false, $overwrite = false, $timestamp = false){
		
		$return_info = array();

		// if form field set
		if(is_array($file)){

			// if file size is within limits
			if($file['size'] > app()->config('upload_max_file_size')){
				$return_info['max_size'] = app()->config('upload_max_file_size');
				error()->raise(2, "Upload failed: file size exceeded maximum.", __FILE__, __LINE__);
				$file['error'] 	= 2;
			}

			// if no error, upload file
    		if($file['error'] == 0){

				// determine the final name of the file
				$new_name = $rename ? $rename : $file['name'];
				
				// add a timestamp into the name
				if($timestamp){
					$exts =  explode(".", $file['name']);
					$new_name = str_replace($exts[(count($exts) - 1)], date("Ymd-Hi") . '.' . $exts[(count($exts) - 1)], $file['name']);
				}
				
				// replace url-unfriendly characters
				$new_name = str_replace(array("&"," "), '_', $new_name);
				$new_name = trim(preg_replace('/[^A-Za-z0-9\.-_]/', '', $new_name));
				$new_name = strtolower(urlencode($new_name));
				
    			// init the file internally
				$this->useFile($this->upload_directory.DS.$new_name);
				$this->file_size = $file['size'];
				$this->file_type = $file['type'];
				$this->tmp_name = $file['tmp_name'];
				$this->getfile_extension();
				
	    		// ensure upload meets allowed file extensions
				if (!$this->typeCheck()){
					error()->raise(2, "The file upload did not meet file type requirements.", __FILE__, __LINE__);
					return "The file upload did not meet file type requirements.";
				}

				// append file info for return
				$return_info['upload_directory'] 	= $this->upload_directory;
				$return_info['upload_success'] 		= false;
				$return_info['server_file_path'] 	= $this->upload_directory . DS . $new_name;
				$return_info['browser_file_path'] 	= $this->browser_url . DS . $new_name;
				$return_info['file_name'] 			= $new_name;
				$return_info['file_type']			= $file['type'];
				$return_info['file_size'] 			= $file['size'];
				$return_info['file_extension']		= strtolower($this->file_extension);
				
				// ensure file doesn't exist, or is overwriteable
				if (file_exists($this->file_path) && !$overwrite){
					return false;
				}

          		// upload the file
          		if(!$status = move_uploaded_file($this->tmp_name, $this->file_path)){
					error()->raise(2, "move_uploaded_file failed. File: " . $this->tmp_name . '  Path: ' . $this->file_path, __FILE__, __LINE__);
          		} else {
            		chmod($this->file_path, 0755);
            		
            		// mark the final upload data, return successful
            		$return_info['upload_success'] 		= true;
            		$return_info['upload_timestamp'] 	= date(DATE_FORMAT);
            		return $return_info;
          		}
        	} else {
				if($file['error'] != 4){
					$msg = $this->uploadError($file['error']);
					error()->raise(2, $msg, __FILE__, __LINE__);
            		return "The file upload was unsuccessful.";
          		}
        	}
		} else {
			error()->raise(2, "Files array was not set properly.", __FILE__, __LINE__);
		}
		return false;
	}


	/**
	 *
	 * @param <type> $err
	 * @return <type>
	 * http://www.php.net/manual/en/features.file-upload.errors.php
	 */
	private function uploadError($err){
		switch($err){
			case 0:
				return '';
				break;
			case 1:
				return 'The uploaded file exceeds the server maximum.';
				break;
			case 2:
				return 'The uploaded file exceeds the form/application maximum.';
				break;
			case 3:
				return 'The uploaded file was only partially uploaded.';
				break;
			case 4:
				return 'The file upload failed due to a missing or corrupt temporary directory.';
				break;
			case 5:
				return 'The file upload failed: Failed to write file to disk.';
				break;
			case 6:
				return 'The file upload failed: File upload stopped by extension.';
				break;
			default:
				return '';
				break;
		}
	}
	

//+-----------------------------------------------------------------------+
//| INFROMATIONAL GET/SET FUNCTIONS
//+-----------------------------------------------------------------------+

	
	/**
	 * Attempts to set the owner of a file
	 * @param string $_owner
	 * @access public
	 */
	public function setOwner($_owner){
		return chown($this->file_path, $_owner);
	}
	
	
	/**
	 * Returns the owner of a file
	 * @return string
	 * @access public
	 */
	public function getOwner(){
		$this->file_owner = fileowner($this->file_path);
		return $this->file_owner;
	}
	
	
	/**
	 * Attempts to set the owning group of a file
	 * @param string $_grp
	 * @access public
	 */
	public function setGroup($_grp){
		return chgrp($this->file_path, $_grp);
	}
	
	
	/**
	 * Returns the owning group of a file
	 * @return string
	 * @access public
	 */
	public function getGroup(){
		$this->file_group = filegroup( $this->file_path);
		return $this->file_group;
	}
	
	
	/**
	 * Returns the size of the file
	 * @return integer
	 * @access public
	 */
	public function getSize(){
		if( !$this->file_size ){
			$this->file_size = @filesize( $this->file_path );
		}
		return $this->file_size;
	}
	
	
	/**
	 * Returns the extension of the file
	 * @return string
	 * @access public
	 */
    public function getfile_extension(){
		  $this->file_extension = strrchr( $this->file_path, "." );
		  $this->file_extension = strtolower($this->file_extension);
		  return $this->file_extension;
    }
	
	
	/**
	 * Returns file type
	 * @return string
	 * @access public
	 */
	public function getType(){
		
		$_mimetypes = array(
         ".ez" => "application/andrew-inset",
         ".hqx" => "application/mac-binhex40",
         ".cpt" => "application/mac-compactpro",
         ".doc" => "application/msword",
         ".bin" => "application/octet-stream",
         ".dms" => "application/octet-stream",
         ".lha" => "application/octet-stream",
         ".lzh" => "application/octet-stream",
         ".exe" => "application/octet-stream",
         ".class" => "application/octet-stream",
         ".so" => "application/octet-stream",
         ".dll" => "application/octet-stream",
         ".oda" => "application/oda",
         ".pdf" => "application/pdf",
         ".ai" => "application/postscript",
         ".eps" => "application/postscript",
         ".ps" => "application/postscript",
         ".smi" => "application/smil",
         ".smil" => "application/smil",
         ".wbxml" => "application/vnd.wap.wbxml",
         ".wmlc" => "application/vnd.wap.wmlc",
         ".wmlsc" => "application/vnd.wap.wmlscriptc",
         ".bcpio" => "application/x-bcpio",
         ".vcd" => "application/x-cdlink",
         ".pgn" => "application/x-chess-pgn",
         ".cpio" => "application/x-cpio",
         ".csh" => "application/x-csh",
         ".dcr" => "application/x-director",
         ".dir" => "application/x-director",
         ".dxr" => "application/x-director",
         ".dvi" => "application/x-dvi",
         ".spl" => "application/x-futuresplash",
         ".gtar" => "application/x-gtar",
         ".hdf" => "application/x-hdf",
         ".js" => "application/x-javascript",
         ".skp" => "application/x-koan",
         ".skd" => "application/x-koan",
         ".skt" => "application/x-koan",
         ".skm" => "application/x-koan",
         ".latex" => "application/x-latex",
         ".nc" => "application/x-netcdf",
         ".cdf" => "application/x-netcdf",
         ".sh" => "application/x-sh",
         ".shar" => "application/x-shar",
         ".swf" => "application/x-shockwave-flash",
         ".sit" => "application/x-stuffit",
         ".sv4cpio" => "application/x-sv4cpio",
         ".sv4crc" => "application/x-sv4crc",
         ".tar" => "application/x-tar",
         ".tcl" => "application/x-tcl",
         ".tex" => "application/x-tex",
         ".texinfo" => "application/x-texinfo",
         ".texi" => "application/x-texinfo",
         ".t" => "application/x-troff",
         ".tr" => "application/x-troff",
         ".roff" => "application/x-troff",
         ".man" => "application/x-troff-man",
         ".me" => "application/x-troff-me",
         ".ms" => "application/x-troff-ms",
         ".ustar" => "application/x-ustar",
         ".src" => "application/x-wais-source",
         ".xhtml" => "application/xhtml+xml",
         ".xht" => "application/xhtml+xml",
         ".zip" => "application/zip",
         ".au" => "audio/basic",
         ".snd" => "audio/basic",
         ".mid" => "audio/midi",
         ".midi" => "audio/midi",
         ".kar" => "audio/midi",
         ".mpga" => "audio/mpeg",
         ".mp2" => "audio/mpeg",
         ".mp3" => "audio/mpeg",
         ".aif" => "audio/x-aiff",
         ".aiff" => "audio/x-aiff",
         ".aifc" => "audio/x-aiff",
         ".m3u" => "audio/x-mpegurl",
         ".ram" => "audio/x-pn-realaudio",
         ".rm" => "audio/x-pn-realaudio",
         ".rpm" => "audio/x-pn-realaudio-plugin",
         ".ra" => "audio/x-realaudio",
         ".wav" => "audio/x-wav",
         ".pdb" => "chemical/x-pdb",
         ".xyz" => "chemical/x-xyz",
         ".bmp" => "image/bmp",
         ".gif" => "image/gif",
         ".ief" => "image/ief",
         ".jpeg" => "image/jpeg",
         ".jpg" => "image/jpeg",
         ".jpe" => "image/jpeg",
         ".png" => "image/png",
         ".tiff" => "image/tiff",
         ".tif" => "image/tif",
         ".djvu" => "image/vnd.djvu",
         ".djv" => "image/vnd.djvu",
         ".wbmp" => "image/vnd.wap.wbmp",
         ".ras" => "image/x-cmu-raster",
         ".pnm" => "image/x-portable-anymap",
         ".pbm" => "image/x-portable-bitmap",
         ".pgm" => "image/x-portable-graymap",
         ".ppm" => "image/x-portable-pixmap",
         ".rgb" => "image/x-rgb",
         ".xbm" => "image/x-xbitmap",
         ".xpm" => "image/x-xpixmap",
         ".xwd" => "image/x-windowdump",
         ".igs" => "model/iges",
         ".iges" => "model/iges",
         ".msh" => "model/mesh",
         ".mesh" => "model/mesh",
         ".silo" => "model/mesh",
         ".wrl" => "model/vrml",
         ".vrml" => "model/vrml",
         ".css" => "text/css",
         ".html" => "text/html",
         ".htm" => "text/html",
         ".asc" => "text/plain",
         ".txt" => "text/plain",
         ".rtx" => "text/richtext",
         ".rtf" => "text/rtf",
         ".sgml" => "text/sgml",
         ".sgm" => "text/sgml",
         ".tsv" => "text/tab-seperated-values",
         ".wml" => "text/vnd.wap.wml",
         ".wmls" => "text/vnd.wap.wmlscript",
         ".etx" => "text/x-setext",
         ".xml" => "text/xml",
         ".xsl" => "text/xml",
         ".mpeg" => "video/mpeg",
         ".mpg" => "video/mpeg",
         ".mpe" => "video/mpeg",
         ".qt" => "video/quicktime",
         ".mov" => "video/quicktime",
         ".mxu" => "video/vnd.mpegurl",
         ".avi" => "video/x-msvideo",
         ".movie" => "video/x-sgi-movie",
         ".ice" => "x-conference-xcooltalk"
		);
		
		// return mime type for extension
		$this->file_type = isset($_mimetypes[$this->file_extension]) ? $_mimetypes[$this->file_extension] : 'application/octet-stream';
		return $this->file_type;
		
	}
}
?>