<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2012 Trellis Development, LLC
 * @since 		2.0
 */

/**
 *
 * @author botskonet
 */
interface AppConfigurable {

	/**
	 * Must be able to load the app configuration 
	 */
	public static function load( $config );
}