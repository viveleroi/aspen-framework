<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @version    	$Revision: 461 $
 * @since 		1.0
 * @revision 	$Id: bootstrap.php 461 2009-04-02 04:56:02Z mbotsko $
 */

/**
 * @abstract Allows messages to be logged to the session and a log of those messages is maintained.
 * @package Aspen_Framework
 */
class Sml {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;

	/**
	 * @var array $sessionMessageArray Holds the array of session messages
	 * @access private
	 */
	private $sessionMessageArray;


 	/**
	 * @abstract Contrucor
	 * @return Sml
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance(); }


	/**
	 * @abstract Adds a new message to the session log
	 * @param string $msg The message
	 * @param boolean $hidden Display to the user or not
	 * @access public
	 */
	public function addNewMessage($msg, $hidden = false){

		/* pull the session if it exists */
		$this->getMessageLog();

		/* add the message */
		$this->sessionMessageArray[] = $msg;

		/* serialize the array and save it to the session */
		$_SESSION['message_log'] = serialize($this->sessionMessageArray);
		$_SESSION['unread_message_flag'] = $hidden ? false : true;
		
		return true;
	}


	/**
	 * @abstract Returns the most recent message from the log
	 * @return mixed
	 * @access public
	 */
	public function getMostRecentMessage(){
		$this->getMessageLog();
		$_recentMsg = array_pop($this->sessionMessageArray);
		return $_recentMsg;
	}


    /**
     * @abstract Returns the array from the session
     * @return array
     * @access public
     */
	public function getMessageLog(){
    	$message_log = $this->APP->params->session->getRaw('message_log');
    	$this->sessionMessageArray = is_string($message_log) ? unserialize($message_log) : array();
    	return $this->sessionMessageArray;
    }


    /**
     * @abstract Prints the messages
     * @access public
     */
	public function printMessage(){
		if($this->APP->params->session->getRaw('unread_message_flag')){
			print sprintf($this->APP->config('sml_message_html'), $this->getMostRecentMessage());
			$_SESSION['unread_message_flag'] = false;
		}
	}
}
?>