<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * Manages user preferences
 * @package Aspen_Framework
 */
class Preferences extends Library {


	/**
	 * Loads user preferences into their session
	 * @access private
	 */
	public function loadUserPreferences(){

		$_SESSION['settings'] =  array();

		$user_id = app()->session->getInt('user_id');

		if($user_id && app()->checkDbConnection()){

			// load sort field
			$pref_model = app()->model->open('config');
			$pref_model->where('user_id', $user_id);
			$pref_model->where('LEFT(config_key, 4)', 'sort');
			$sorts = $pref_model->results();

			if($sorts){
				foreach($sorts as $sort){
					$_SESSION['settings']['sorts'][$sort['config_key']] = $sort;
				}
			}
		}

		app()->refreshCage('session');

	}


	/**
	 * Adds a new sort preference
	 * @param string $location
	 * @param string $field
	 * @param string $dir
	 * @access public
	 */
	public function addSort($location = false, $field = false, $dir = 'ASC'){

		if($user_id = app()->session->getInt('user_id')){

			$pref_model = app()->model->open('config');

			// remove any old entry for this location
			$sql = sprintf('
						DELETE FROM config
						WHERE user_id = "%s"
						AND (config_key = "sort.%s.field"
						OR config_key = "sort.%2$s.dir" )',
						$user_id, $location);
			$pref_model->query($sql);

			// add in a new entry
			$pref_model->insert(array('user_id' => $user_id, 'config_key' => 'sort.'.$location.'.field', 'current_value' => $field));
			$pref_model->insert(array('user_id' => $user_id, 'config_key' => 'sort.'.$location.'.dir', 'current_value' => $dir));

		}
	}


	/**
	 * Returns a sort preference
	 * @param string $location
	 * @param string $field
	 * @param string $default
	 * @param string $dir
	 * @return array
	 * @access public
	 */
	public function getSort($location, $field = false, $default = 'id', $dir = 'ASC'){

		$sort = array('sort_by'=>$default,'sort_direction'=>strtoupper($dir));

		if(app()->session->isArray('settings')){

			$settings = app()->session->getArray('settings');

			$loc_key = 'sort.'.$location;
			if(isset($settings['sorts'][$loc_key.'.field'])){

				$sort['sort_by']		= $settings['sorts'][$loc_key.'.field']['current_value'];
				$sort['sort_direction']	= strtoupper($settings['sorts'][$loc_key.'.dir']['current_value']);

				//If the field arg not matched, don't return anything
				if($field && $sort['sort_by'] != $field){
					$sort = false;
				}
			}
		}

		return $sort;

	}


	/**
	 * Pulls and allows easy POSTing of configuration values.
	 */
	public function edit($user_id = NULL){

		// Load the prefs we're allowed to edit
		$edit_prefs = app()->config('preference_configs_to_edit');

		// Set the current/default values
		$record = array();
		foreach($edit_prefs as $pref){
			$record[$pref] = app()->settings->getConfig($pref, $user_id);
		}

		// process the form if submitted
		if(app()->post->keyExists('preferences-submit')){
			$config = app()->model->open('config');
			foreach($record as $field => $existing_value){
				$record[$field] = app()->post->getRaw($field);
				$config->query( sprintf('DELETE FROM config WHERE config_key = "%s" AND user_id = "%s"', $field, $user_id) );
				$config->insert( array('current_value'=>$record[$field],'config_key'=>$field,'user_id'=>$user_id));
			}
			app()->sml->say('Your user preferences have been saved successfully.', true);
		}

		return new Prefs($record);

	}
}


/**
 *
 */
class Prefs {

	/**
	 * @var array Preferences
	 */
	protected $prefs;


	/**
	 *
	 * @param <type> $prefs
	 */
	public function  __construct($prefs) {
		$this->prefs = $prefs;
	}


	/**
	 *
	 * @param <type> $key
	 * @param <type> $default
	 * @return <type>
	 */
	public function get($key, $default = false){
		if(array_key_exists($key, $this->prefs)){
			return $this->prefs[$key];
		}
		return $default;
	}


	/**
	 *
	 * @param <type> $key
	 * @param <type> $val
	 * @return <type>
	 */
	public function checked($key, $val){
		if(array_key_exists($key, $this->prefs)){
			return ($this->prefs[$key] == $val ? ' checked="checked"' : '');
		}
		return false;
	}
}
?>