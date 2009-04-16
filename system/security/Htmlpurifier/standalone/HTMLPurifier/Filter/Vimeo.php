<?php

class HTMLPurifier_Filter_Vimeo extends HTMLPurifier_Filter
{
    
    public $name = 'Vimeo';
    
    public function preFilter($html, $config, $context) {
		$pre_regex = '#<object width="([0-9]+)" height="([0-9]+)[^>]+>.+?'.'http://vimeo.com/moogaloop.swf\?clip_id=([0-9\-_]+).+?</object>#s';
		$pre_replace = '<span class="vimeo-embed" style="width:\1px;height:\2px;">\3</span>';
		return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="vimeo-embed" style="width:([0-9]+)px;height:([0-9]+)px;">([A-Za-z0-9\-_]+)</span>#';
        $post_replace = '<object width="\1" height="\2" '.
            'data="http://vimeo.com/moogaloop.swf?clip_id=\3">'.
            '<param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=\3"></param>'.
            '<param name="wmode" value="transparent"></param>'.
            '<!--[if IE]>'.
            '<embed src="http://vimeo.com/moogaloop.swf?clip_id=\3"'.
            'type="application/x-shockwave-flash"'.
            'wmode="transparent" width="\1" height="\2" />'.
            '<![endif]-->'.
            '</object>';
        return preg_replace($post_regex, $post_replace, $html);
    }
}
?>