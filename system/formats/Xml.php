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
 * @abstract Common basic xml formatting functions.
 * @package Aspen_Framework
 */
class Xml {

	/**
	 * @abstract Encodes strings for xml. This is essentially depracated with DOMDocument
	 * @param string $string
	 * @return string
	 */
	public function encode_for_xml($string){

		$string = html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
		$string = htmlentities($string, ENT_NOQUOTES, 'UTF-8');

		//	manually add back in html
		$string = str_replace("&lt;", "<", $string);
		$string = str_replace("&gt;", ">", $string);

		return $string;
	}
	
	
	/**
	 * @abstract Turns an array into a basic xml response
	 * @param array $inc_array
	 * @return string
	 */
	public function arrayToXml($inc_array){
	
		if(is_array($inc_array)){
			$doc = new DOMDocument();
	  		$doc->formatOutput = true;
	  		$response = $doc->appendChild( $doc->createElement( "response" ) );
			
			if(is_array($inc_array)){
				foreach($inc_array as $key => $value){
	
					$item = $doc->createElement( $key );
					$item->appendChild( $doc->createTextNode( $value ));
					$response->appendChild($item);
						
				}
			}
			
			return $doc->saveXML();
		}
		return false;
	}
}
?>