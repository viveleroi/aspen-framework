<?php

/**
 * Description of 
 * @todo cleanup this comment
 * @author botskonet
 */
class Activityrender_html extends Library {

	/**
	 * @var string HTML template container for each activity entry.
	 */
	protected $html_template = '<li><span class="{flag-css}">{flag-css}</span> <span class="date">{date}</span> <span class="user">{user}</span> {message}</li>';

	/**
	 * 
	 */
	protected $render_base;


	/**
	 *
	 * @param <type> $ac_arr
	 */
	public function __construct($rb){
		parent::__construct();
		$this->ac_array = $rb->get_activity();
		$this->render_base = $rb;
	}


	/**
	 *
	 * @return <type>
	 */
	public function render(){
		$output = '<ul>';
		foreach($this->ac_array as $ac){
			$output .= $this->formatChangeset($ac);
		}
		$output .= '<ul>';

		return $output;
	}


	/**
	 * Formats an issue change.
	 *
	 * @param array $ac
	 * @return string
	 * @access private
	 */
	protected function formatChangeset($changeset){

		// The first item will be used as our summary
		$ac = $changeset[0];

		// Pull base render
		$render = $this->format_base($ac);

		// build standard message
		$msg = 'With table "%s", record %s was %s.';
		$msg = sprintf($msg, $ac['table_name'], $ac['record_id'], $this->render_base->getActivityType($ac['activity_type']));
		$render = str_replace('{message}',$msg,$render);

		// add the sublist
		if($ac['activity_type'] == 'update'){
			$render .= $this->formatSubChangeset($changeset);
		}

		return $render;

	}


	/**
	 * Formats a project change.
	 *
	 * @param array $ac
	 * @return string
	 * @access private
	 */
	protected function format_base($ac){

		// build html
		$html = $this->html_template;
		$html = str_replace('{flag-css}',$this->render_base->getActivityCss($ac['activity_type']),$html);
		$html = str_replace('{date}',$this->render_base->formatDate($ac['timestamp']),$html);

		$user = empty($ac['nice_name']) ? 'Unknown' : $ac['nice_name'];
		$html = str_replace('{user}',$user,$html);

		return $html;

	}



	/**
	 *
	 * @return <type>
	 */
	protected function formatSubChangeset($changes){

		$html = '';

		$chg_msg = '<li>%s changed from "%s" to "%s"</li>';

		if($changes){
			$html .= '<ul>';
			foreach($changes as $change){
				$field		= $change['field_name'];
				$old_val	= $change['old_value'];
				$new_val	= $change['new_value'];
				$html .= sprintf($chg_msg, ucwords($field), $old_val, $new_val);
			}
			$html .= '</ul>';
		}

		return $html;
	}
}
?>