<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2012 Trellis Development, LLC
 * @since 		2.0
 */

/**
 * Object handler for links and urls
 *
 * @author botskonet
 */
class LinkSort {
	
	/**
	 *
	 * @var type 
	 */
	protected $_sort_location;
	
	/**
	 *
	 * @var type 
	 */
	protected $_url;
	
	
	/**
	 * 
	 * @param type $url
	 * @param type $loc
	 */
	public function __construct( $url, $loc ) {
		$this->_sort_location = $loc;
		$this->_url = $url;
	}
	
	
	/**
	 * 
	 * @param type $title
	 * @param type $sort_by
	 * @param type $add_class
	 * @return type
	 */
	public function link($title, $sort_by, $add_class){

		$sort = app()->prefs->getSort($this->_sort_location, $sort_by);

		// determine the sort direction
		$new_direction = $sort['sort_direction'] == "ASC" ? "DESC" : "ASC";
		
		// add class
		$add_class = $add_class ? ' '.$add_class : '';

		// determine the proper class, if any
		$class = 'sortable';
		if($sort['sort_by'] == $sort_by){
			$class = strtolower($new_direction);
		}

		// build proper url
		$url = $this->_url.'?'.http_build_query(array(
									'sort_location'=>$this->_sort_location,
									'sort_by'=>$sort_by,
									'sort_direction'=>$new_direction), '', '&amp;');

		// create the link
		$html = sprintf('<a href="%s" title="%s" class="%s">%s</a>',
								$url,
								'Sort ' . Link::encodeTextEntities($title) . ' column ' . ($new_direction == 'ASC' ? 'ascending' : 'descending'),
								$class.$add_class,
								Link::encodeTextEntities($title)
							);

		return $html;

	}
}