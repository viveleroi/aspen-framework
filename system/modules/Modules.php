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

		if($guid && $this->APP->checkDbConnection()){

			$autoload	= array();
			$installed	= $this->APP->getInstalledModuleGuids();

			foreach($installed as $module){
				$reg = $this->APP->moduleRegistry($module);
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
					$this->APP->loadModule($load_guid);
				}
			}
		}
	}


	/**
	 * Returns an array of installed module GUIDs that are not part of the standard install
	 * @return array
	 */
	public function getNonBaseModules(){

		$nonbase = array();

		if($this->APP->checkDbConnection()){

			// find any modules with autoload set to current guid
			$model = $this->APP->model->open('modules');
			$model->orderBy('sort_order');
			$modules = $model->results();

			if($modules){
				foreach($modules as $module){
					$reg = $this->APP->moduleRegistry($module['guid']);
					if(isset($reg->installable) && $reg->installable){
						$nonbase[] = $module['guid'];
					}
				}
			}
		}

		return $nonbase;

	}


	/**
	 * Returns an array of ALL module GUIDs that are not part of the standard install, whether they're installed or not
	 * @return array
	 */
	public function getAllNonBaseModules(){

		$nonbase = array();

		foreach($this->APP->getModuleRegistry() as $module){
			if(isset($module->installable) && $module->installable){
				$nonbase[] = (string)$module->guid;
			}
		}

		return $nonbase;

	}
}
?>