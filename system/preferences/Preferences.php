<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * @abstract Manages user preferences
 * @package Aspen_Framework
 */
class Preferences {

	/**
	 * @var object $APP Holds our original application
	 * @access private
	 */
	protected $APP;


	/**
	 * @abstract Constructor
	 * @access private
	 */
	public function __construct(){ $this->APP = get_instance(); }


	/**
	 * @abstract Loads user preferences into their session
	 * @access private
	 */
	public function loadUserPreferences(){

		$_SESSION['settings'] =  array();

//		if($this->APP->params->session->getInt('user_id', false) && $this->APP->checkDbConnection()){
//
//			// load sort settings
//			$pref_model = $this->APP->model->open('preferences_sorts');
//			$pref_model->where('user_id', $this->APP->params->session->getInt('user_id'));
//			$sorts = $pref_model->results();
//
//			if($sorts['RECORDS']){
//				foreach($sorts['RECORDS'] as $sort){
//
//					$sort_pref = array('sort_by' => $sort['sort_by'], 'sort_direction' => $sort['direction']);
//					$_SESSION['settings']['sorts'][$sort['location']] = $sort_pref;
//
//				}
//			}
//		}

		$this->APP->params->refreshCage('session');

	}


	/**
	 * @abstract Adds a new sort preference
	 * @param string $location
	 * @param string $field
	 * @param string $dir
	 * @access public
	 */
	public function addSort($location = false, $field = false, $dir = 'ASC'){

//		if($this->APP->params->session->getInt('user_id', false)){
//
//			$pref_model = $this->APP->model->open('preferences_sorts');
//
//			// remove any old entry for this location
//			$sql = sprintf('DELETE FROM preferences_sorts WHERE user_id = "%s" AND location = "%s"',
//														$this->APP->params->session->getInt('user_id'), $location);
//			$this->APP->model->query($sql);
//
//			// add in a new entry
//			$settings = array(
//							'user_id' => $this->APP->params->session->getInt('user_id'),
//							'location' => $location,
//							'sort_by' => $sort,
//							'direction' => $dir
//						);
//			$pref_model->insert($settings);
//
//		}
	}


	/**
	 * @abstract Returns a sort preference
	 * @param string $location
	 * @param string $field
	 * @param string $default
	 * @param string $dir
	 * @return array
	 * @access public
	 */
	public function getSort($location, $field = false, $default = 'id', $dir = 'ASC'){

		$sort = array('sort_by'=>$default,'sort_direction'=>$dir,'is_default'=>true);

		if($this->APP->params->session->getInt('user_id', false) && $this->APP->params->session->getRaw('settings', false)){

			$settings = $this->APP->params->session->getRaw('settings');

			if(isset($settings['sorts'][$location])){
				if($field){
					$sort = $settings['sorts'][$location]['sort_by'] == $field ? $settings['sorts'][$location] : false;
				} else {
					$sort = $settings['sorts'][$location];
				}
			}
		}

		return $sort;

	}
}
?>