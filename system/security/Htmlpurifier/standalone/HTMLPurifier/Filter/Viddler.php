<?php

class HTMLPurifier_Filter_Viddler extends HTMLPurifier_Filter
{
    
    public $name = 'Viddler';
    
    public function preFilter($html, $config, $context) {
		$pre_regex = '#<object width="([0-9]+)" height="([0-9]+)" id="viddler_([A-Za-z0-9\-_]+)[^>]+>.+?'.'</object>#s';
		$pre_replace = '<span class="viddler-embed" style="width:\1px;height:\2px;">\3</span>';
		return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="viddler-embed" style="width:([0-9]+)px;height:([0-9]+)px;">([A-Za-z0-9\-_]+)</span>#';
        $post_replace = '<object width="\1" height="\2" id="viddler_\3">'.
			'<param name="movie" value="http://www.viddler.com/player/\3/" />'.
			'<param name="allowScriptAccess" value="always" />'.
			'<param name="allowFullScreen" value="true" />'.
			'<embed src="http://www.viddler.com/player/\3/" width="\1" height="\2" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" name="viddler_\3" ></embed>'.
			'</object>';
        return preg_replace($post_regex, $post_replace, $html);
    }
}
?>