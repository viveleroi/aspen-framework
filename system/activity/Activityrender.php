<?php

/**
 * Description of
 * @todo cleanup this comment
 * @author botskonet
 */
class Activityrender extends Library {

	/**
	 * @var array Limits to the unique activity counts
	 */
	protected $activity_limit = 25;

	/**
	 * @var array All recent activity
	 */
	protected $all_recent_activity = array();


	protected $table;

	protected $record_id;

	protected $token;


	/**
	 *
	 * @param <type> $ac_arr
	 */
	public function __construct($table = false, $record_id = false, $token = false){
		parent::__construct();
		$this->table = $table;
		$this->record_id = $record_id;
		$this->token = $token;
	}


	/**
	 * Returns an array of recent activity for display.
	 *
	 * @return string
	 * @access public
	 */
	public function get_activity(){
		$this->get_all_recent_activity();
		return $this->all_recent_activity;
	}


	/**
	 *
	 * @param <type> $limit
	 */
	public function setActivityLimit($limit){
		if(is_int($limit)){
			$this->activity_limit = $limit;
		}
	}


	/**
	 *
	 * @param <type> $limit
	 */
	public function getActivityLimit($limit){
		$this->activity_limit = $limit;
	}


	/**
	 * Returns all recent activity within the last week. This list is then sorted
	 * and pruned down to the max number of entries.
	 *
	 * @access private
	 */
	protected function get_all_recent_activity(){

		$this->all_recent_activity = array();

		$ac = app()->model->open('activity');
		$ac->leftJoin('users', 'id', 'user_id', array('username','first_name','last_name'));
		$ac->inPastXDays('timestamp');

		if($this->table){
			$ac->where('table_name', $this->table);
		}

		if($this->record_id){
			$ac->where('record_id', $this->record_id);
		}

		if($this->token){
			$ac->where('changeset_hash', $token);
		}

		$ac->orderBy('timestamp','DESC');
		$ac->limit(0,150);
		$activities = $ac->results();

		$count = 0;
		if($activities){
			foreach($activities as $activity){

				if($count > $this->activity_limit){
					break;
				}

				// use a shortened key for this specific activity
				$key = substr($activity['changeset_hash'], 0, 5);

				// unique hash count
				if(!array_key_exists($key, $this->all_recent_activity)){
					$count++;
				}

				// We then save the record to the array with that key,
				// which ensures we don't have duplicates as well
				// allows us to recognize groups of related
				// activities
				$this->all_recent_activity[$key][] = $activity;
			}
		}
		return (bool)$activities;
	}


	/**
	 * Returns the language to be used for each activity type. This ensures
	 * it's consistent.
	 *
	 * @todo Can't this just be a language var?
	 * @param string $type
	 * @return string
	 * @access private
	 */
	public function getActivityType($type){
		switch($type){
			case 'insert':
				return 'inserted';
				break;
			case 'update':
				return 'updated';
				break;
			case 'delete':
				return 'deleted';
				break;
		}
		return $type;
	}


	/**
	 * Returns the css class and text to be used for each activity type.
	 *
	 * @param string $type
	 * @return string
	 * @access private
	 */
	public function getActivityCss($type){

		switch($type){
			case 'insert':
				return 'new';
				break;
			case 'update':
				return 'upd';
				break;
			case 'delete':
				return 'del';
				break;
		}
	}


	/**
	 * Formats the date. Ignores the date if it's from today.
	 *
	 * @param string $date
	 * @return string
	 * @access private
	 */
	public function formatDate($date){

		$today = app()->template->pref_date(gmdate('Y-m-d H:i:s'), 'Y-m-d');

		if(app()->template->pref_date($date, 'Y-m-d') == $today){
			return app()->template->pref_date($date, 'g:i a');
		} else {
			return app()->template->pref_date($date, 'm/d @ g:i a');
		}
		return $date;
	}


	/**
	 * Returns the real user
	 * @param integer $user
	 * @return string
	 */
	public function getRealUser($user){
		if($user){
			$user = app()->model->open('users', $user);
			return $user['first_name'].' '.$user['last_name'];
		}
		return '(empty)';
	}
}
?>