<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
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