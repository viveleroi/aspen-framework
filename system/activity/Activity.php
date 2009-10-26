<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.1-beta-1-16
 */

/**
 * Base parent class for application libraries, sets up app reference
 * @package Aspen_Framework
 */
class Activity extends Library {


	/**
	 * Logs an activity to the watch table.
	 * @param string $key
	 * @param string $table
	 * @param string $field
	 * @param string $old_value
	 * @param string $new_value
	 * @param string> $message
	 */
	public function logChange($key = '', $type = '', $table = '', $record_id = '', $field = '', $old_value = '', $new_value = ''){

		$activity = $this->APP->model->open('activity');
		$res = $activity->insert(array(
								'changeset_hash'=>$key,
								'activity_type'=>$type,
								'table_name'=>$table,
								'record_id' => $record_id,
								'field_name'=>$field,
								'old_value'=>$old_value,
								'new_value'=>$new_value)
							);
	}
}
?>