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
class Link {
	
	/**
	 * @var string Holds the template string for a LINK css include
	 */
	const HTML_ELM_LINK = '<a href="{url}" title="{title}"{classes}>{text}</a>';
	
	/**
	 *
	 * @var type 
	 */
	protected $classes = array();
	
	/**
	 *
	 * @var type 
	 */
	protected $path;
	
	/**
	 *
	 * @var type 
	 */
	protected $text;
	
	/**
	 *
	 * @var type 
	 */
	protected $url;
	
	
	/**
	 * 
	 * @param type $url
	 * @return \Link
	 */
	public static function href( $text, $url ){
		$l = new Link();
		$l->setText($text);
		$l->setUrl($url);
		return $l;
	}
	
	
	/**
	 * 
	 * @param type $url
	 * @return \Link
	 */
	public static function path( $text, $path, $bits = false ){
		$l = new Link();
		$l->setText($text);
		$l->setPath($path);
		$l->setUrl( Url::path($path, $bits) );
		return $l;
	}
	
	
	/**
	 * 
	 * @param type $url
	 */
	public function setUrl($url){
		$this->url = $url;
		return $this;
	}
	
	
	/**
	 * 
	 * @param type $url
	 */
	public function setText($text){
		$this->text = strip_tags($text);
		return $this;
	}
	
	
	/**
	 * 
	 * @param type $url
	 */
	public function setPath($path){
		$this->path = $path;
		return $this;
	}
	
	
	/**
	 * 
	 * @param type $class
	 */
	public function addClass($class){
		$this->classes[] = $class;
		return $this;
	}
	
	
	/**
	 * 
	 */
	protected function setAt(){
		$u = new Url();
		$r = $u->parseNamespacePath($this->path);
		// @todo re-implement: if(user()->userHasAccess($r['module'], $r['method'], $r['interface'])){
		// highlight the link if the user is at the page
		if($r['method'] == router()->method() && ucwords($r['module']) == router()->cleanModule(router()->module())){
			$this->addClass(config()->get('active_link_class_name'));
		}
	}
	
	
	/**
	 * Encodes entities that appear in text only, not html
	 * @param string $string
	 * @return string
	 * @access public
	 */
	public static function encodeTextEntities($string){
		return str_replace("&", "&#38;", $string);
	}
	
	
	/**
	 * 
	 * @return type
	 */
	public function __toString(){
		$link = str_replace('{url}', $this->url, self::HTML_ELM_LINK);
		$link = str_replace('{text}', $this->text, $link);
		$link = str_replace('{title}', $this->text, $link);
		$link = str_replace('{classes}', $this->buildClasses(), $link);
		return Link::encodeTextEntities($link);
	}
	
	
	/**
	 * 
	 * @return type
	 */
	protected function buildClasses(){
		if(!empty($this->classes)){
			return ' class="'.implode(' ', $this->classes).'"';
		}
		return '';
	}
}