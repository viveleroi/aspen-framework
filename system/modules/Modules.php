<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Manages modules, registries, etc
 * @package Aspen_Framework
 */
class Modules extends Library {
	

	/**
	 * Loads any modules that are hooked to current module
	 * @param string $guid
	 * @access public
	 */
	public function callModuleHooks($guid = false){

		if($guid && app()->checkDbConnection()){

			$autoload	= array();
			$modules	= app()->getModuleRegistry();

			foreach($modules as $reg){
				if(isset($reg->hook)){
					$att = $reg->hook->attributes();
					if((string)$att->type == "module"){
						$autoload[] = $module;
					}
				}
			}

			// if modules found, let's load them!
			if(count($autoload) > 0){
				foreach($autoload as $load_guid){
					app()->loadModule($load_guid);
				}
			}
		}
	}
}
?>