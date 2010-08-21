<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Allows messages to be logged to the session and a log of those messages is maintained.
 * @package Aspen_Framework
 */
class Sml extends Library {

	/**
	 * @var array $sessionMessageArray Holds the array of session messages
	 * @access private
	 */
	private $sessionMessageArray;


	/**
	 * Adds a new message to the session log
	 * @param string $msg The message
	 * @param string $class CSS Class for display
	 * @access public
	 */
	public function say($msg, $class = 'success'){

		// Interpret boolean values
		if($class === 0 || $class === false){
			$class = 'error';
		}

		if($class === 1 || $class === true){
			$class = 'success';
		}

		/* pull the session if it exists */
		$this->getMessageLog();

		/* add the message */
		$this->sessionMessageArray[] = array('message'=>$msg, 'class'=>$class);

		/* serialize the array and save it to the session */
		$_SESSION['message_log'] = serialize($this->sessionMessageArray);
		$_SESSION['unread_message_flag'] = true;

		/* refresh the session cage */
		app()->refreshCage('session');

		return true;
	}


	/**
	 * Returns the most recent message from the log
	 * @return mixed
	 * @access public
	 */
	public function getMostRecentMessage(){
		$this->getMessageLog();
		return array_pop($this->sessionMessageArray);
	}


    /**
     * Returns the array from the session
     * @return array
     * @access public
     */
	public function getMessageLog(){
    	$message_log = app()->session->getRaw('message_log');
    	$this->sessionMessageArray = is_string($message_log) ? unserialize($message_log) : array();
    	return $this->sessionMessageArray;
    }


    /**
     * Prints the messages
     * @access public
     */
	public function printMessage(){
		if(app()->session->getInt('unread_message_flag')){
			$message = $this->getMostRecentMessage();
			printf(app()->config('sml_message_html'), $message['message'], $message['class']);
			$_SESSION['unread_message_flag'] = false;
		}
	}
}
?>