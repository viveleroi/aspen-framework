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
 * @abstract Manages modules, registries, etc
 * @package Aspen_Framework
 */
class Modules {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	private $APP;


	/**
	 * @abstract Constructor
	 * @return Modules
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance(); }
	
	
	/**
	 * @abstract Loads any modules that are hooked to current module
	 * @param string $guid
	 * @access public
	 */
	public function callModuleHooks($guid = false){
		
		if($guid && $this->APP->checkDbConnection()){
			
			$autoload = array();
			
			// find any modules with autoload set to current guid
			$this->APP->model->select('modules');
			$this->APP->model->where('autoload_with', $guid);
			$modules = $this->APP->model->results();
			
			if($modules['RECORDS']){
				foreach($modules['RECORDS'] as $module){
					$autoload[] = $module['guid'];
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
	 * @abstract Identifies a module as autoload when parent module is loaded
	 * @param string $parent_guid
	 * @param string $depen_guid
	 * @return boolean
	 * @access public
	 */
	public function registerModuleHook($parent_guid = false, $depen_guid = false){

		$sql = sprintf('UPDATE modules SET autoload_with = "%s" WHERE guid = "%s"',
							$this->APP->security->dbescape($parent_guid),
							$this->APP->security->dbescape($depen_guid));
		
		return $this->APP->model->query($sql);
		
	}
	
	
	/**
	 * @abstract Returns an array of module GUIDs that are not part of the standard install
	 * @return array
	 */
	public function getNonBaseModules(){
		
		$nonbase = array();
		
		if($this->APP->checkDbConnection()){
		
			// find any modules with autoload set to current guid
			$this->APP->model->select('modules');
			$this->APP->model->where('is_base_module', 0);
			$this->APP->model->orderBy('sort_order');
			$modules = $this->APP->model->results();
			
			if($modules['RECORDS']){
				foreach($modules['RECORDS'] as $module){
					$nonbase[] = $module['guid'];
				}
			}
		}
		
		return $nonbase;
		
	}
	
	
	/**
	 * @abstract Returns an array of module GUIDs that are not part of the standard install, whether they're installed or not
	 * @return array
	 */
	public function getAllNonBaseModules(){
		
		$nonbase = array();
		
		foreach($this->APP->getModuleRegistry() as $module){
		
			// find any modules with autoload set to current guid
			$this->APP->model->select('modules');
			$this->APP->model->where('guid', (string)$module->guid);
			$modules = $this->APP->model->results();
	
			if($modules['RECORDS']){
				foreach($modules['RECORDS'] as $nonbasemod){
					if(!$nonbasemod['is_base_module']){
						$nonbase[] = $module->guid;
					}
				}
			} else {
				$nonbase[] = $module->guid;
			}
		}
		
		return $nonbase;
		
	}
}
?>