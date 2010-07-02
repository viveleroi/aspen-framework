<?php

/**
 * Description of
 * @todo cleanup this comment
 * @author botskonet
 */
class Activityrender_html extends Library {

	/**
	 * @var string HTML template container for each subset activity entry list.
	 */
	protected $html_template = '<ul>%s</ul>';

	/**
	 * @var string HTML template container for each activity entry.
	 */
	protected $html_template_li = '<li><span class="{flag-css}">{flag-css}</span> <span class="date">{date}</span> <span class="user">{user}</span> {message}</li>';

	/**
	 * @var string HTML template container for each subset activity entry list.
	 */
	protected $sub_html_template = '<ul>%s</ul>';

	/**
	 * @var string HTML template container for each subset activity entry.
	 */
	protected $sub_html_template_li = '<li>{field} changed from "{old_val}" to "{new_val}"</li>';


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
		$output = '';
		foreach($this->ac_array as $ac){
			$output .= $this->formatChangeset($ac);
		}
		return sprintf($this->html_template, $output);
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
		$html = $this->html_template_li;
		$html = str_replace('{flag-css}',$this->render_base->getActivityCss($ac['activity_type']),$html);
		$html = str_replace('{date}',$this->render_base->formatDate($ac['timestamp']),$html);

		$user = empty($ac['first_name']) ? 'Unknown' : $ac['first_name'];
		$html = str_replace('{user}',$user,$html);

		return $html;

	}



	/**
	 *
	 * @return <type>
	 */
	protected function formatSubChangeset($changes){
		$html = '';
		if($changes){
			foreach($changes as $change){
				$html .= sprintf($this->sub_html_template_li, ucwords($change['field_name']), $change['old_value'], $change['new_value']);
			}
		}
		return sprintf($this->sub_html_template, $html);
	}
}
?>