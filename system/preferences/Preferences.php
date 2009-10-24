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
class Preferences {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	protected $APP;


	/**
	 * Constructor
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance(); }


	/**
	 * Loads user preferences into their session
	 * @access private
	 */
	public function loadUserPreferences(){

		$_SESSION['settings'] =  array();

		if($user_id = $this->APP->params->session->getInt('user_id') && $this->APP->checkDbConnection()){

			// load sort field
			$pref_model = $this->APP->model->open('config');
			$pref_model->where('user_id', $user_id);
			$pref_model->where('LEFT(config_key, 4)', 'sort');
			$sorts = $pref_model->results();

			if($sorts['RECORDS']){
				foreach($sorts['RECORDS'] as $sort){
					$_SESSION['settings']['sorts'][$sort['config_key']] = $sort;
				}
			}
		}

		$this->APP->params->refreshCage('session');

	}


	/**
	 * Adds a new sort preference
	 * @param string $location
	 * @param string $field
	 * @param string $dir
	 * @access public
	 */
	public function addSort($location = false, $field = false, $dir = 'ASC'){

		if($user_id = $this->APP->params->session->getInt('user_id')){

			$pref_model = $this->APP->model->open('config');

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

		if($this->APP->params->session->getInt('user_id', false) && $this->APP->params->session->getRaw('settings', false)){

			$settings = $this->APP->params->session->getRaw('settings');

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
		$edit_prefs = $this->APP->config('preference_configs_to_edit');

		// Set the current/default values
		$record = array();
		foreach($edit_prefs as $pref){
			$record[$pref] = $this->APP->settings->getConfig($pref, $user_id);
		}

		// process the form if submitted
		if($this->APP->form->isSubmitted('post','preferences-submit')){
			$config = $this->APP->model->open('config');
			foreach($record as $field => $existing_value){
				$record[$field] = $this->APP->params->post->getRaw($field);
				$config->query( sprintf('DELETE FROM config WHERE config_key = "%s" AND user_id = "%s"', $field, $user_id) );
				$config->insert( array('current_value'=>$record[$field],'config_key'=>$field,'user_id'=>$user_id));
			}
			$this->APP->sml->say('Your user preferences have been saved successfully.', true);
		}

		return $record;

	}
}
?>