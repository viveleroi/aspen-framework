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
class Url {

    /**
     *
     * @var type
     */
    protected $bits;

    /**
     *
     * @var type
     */
    protected $path;

    /**
     *
     * @var type
     */
    protected $query_string_bits = array();

    /**
     *
     * @var type
     */
    protected $urlencode = true;

    /**
     *
     * @var type
     */
    protected $url;


    /**
     *
     * @param type $path
     * @param type $bits
     * @return \Url
     */
    public static function path($path = false, $bits = false, $hash = false){
        $u = new Url($path,$bits,$hash);
        return $u;
    }


    /**
     *
     * @param type $path
     * @param type $bits
     */
    public function __construct($path = false, $bits = false, $hash = false) {
        $this->path = $path;
        $this->bits = $bits;
        $this->hash = $hash;
    }


    /**
     * Sets the bits of the url to the id only
     * @param string $method
     * @param string $module
     * @param string $interface
     * @return string
     * @access public
     */
    public function action(){
        if(router()->arg(1) && !$this->bits){
            $this->bits = array('id' => router()->arg(1));
        }
        return $this;
    }


    /**
     *
     * @param type $urlencode
     */
    public function setUrlencode($urlencode = true){
        $this->urlencode = $urlencode;
        return $this;
    }


    /**
     *
     * @param type $bits
     */
    public function query( $bits ){
        $this->query_string_bits = array_merge($this->query_string_bits, $bits);
        return $this;
    }


    /**
     * Parses an interface/module/method path for the individual parts
     * @param string $path
     * @return string
     */
    public function parseNamespacePath($path = false, $type = 'method'){
        if($path){
            $path = explode('/',urldecode($path));
            $path = is_array($path) ? array_reverse($path) : $path;
        }

        if(count($path) > 1 || empty($path)){

            $r['method'] = (is_array($path) && isset($path[0]) ? $path[0] : router()->method());
            $r['module'] = (is_array($path) && isset($path[1]) ? router()->cleanModule($path[1]) : strtolower(router()->cleanModule(router()->module())));
            $r['interface'] = (is_array($path) && isset($path[2]) ? strtolower($path[2]) : (LS != '' && LS != 'app' ? LS : ''));
        } else {
            if($type == 'module'){
                $r['module'] = router()->cleanModule($path[0]);
                $r['method'] = false;
            } else {
                $r['module'] = router()->module();
                $r['method'] = $path[0];
            }
            $r['interface'] = (LS != '' && LS != 'app' ? LS : '');
        }
        return $r;
    }


    /**
     *
     */
    protected function buildUrl(){


        $r = $this->parseNamespacePath($this->path);
        $url = router()->interfaceUrl($r['interface']);

        // if mod rewrite/clean urls are off
        if(!config()->get('enable_mod_rewrite')){

            $url .= sprintf('/index.php?module=%s&method=%s', $r['module'], $r['method']);

            if(is_array($this->bits)){
                foreach($this->bits as $bit => $value){
                    if(is_array($value)){
                        foreach($value as $key => $val){
                            $url .= '&' . $bit . '[' . $key . ']=' . $this->urlencode($val);
                        }
                    } else {
                        $url .= '&' . $bit . '=' . $this->urlencode($value);
                    }
                }
            }

            // append all forced query string bits
            $url .= http_build_query($this->query_string_bits);

        } else {

            // Determine if there are any routes that need to be used instead
            $routes = config()->get('routes');

            $route_mask = false;
            if(is_array($routes)){
                foreach($routes as $mask => $route){
                    if(strtolower($route['module']) == strtolower($r['module']) && strtolower($route['method']) == strtolower($r['method'])){
                        // if the interface is also set, it must match
                        if(isset($route['interface'])){
                            if(strtolower($route['interface']) == strtolower($r['interface'])){
                                $route_mask = $mask;
                                $url .= '/'.$route['uri'];
                            }
                        } else {
                            $route_mask = $mask;
                            $url .= '/'.$route['uri'];
                        }
                    }
                }
            }

            // Otherwise, just build it as normal
            if(!$route_mask){
                if($r['module'] != strtolower(config()->get('default_module')) || !empty($this->bits)){
                    $url .= sprintf('/%s', $r['module']);
                }
                $url .= $r['method'] != config()->get('default_method') || is_array($this->bits) ? sprintf('/%s', $r['method']) : '';
            }

            if(is_array($this->bits)){
                foreach($this->bits as $bit => $value){
                    if(is_array($value)){
                        foreach($value as $key => $val){
                            $url .= '/' . $bit . '[' . $key . ']=' . $this->urlencode($val);
                        }
                    } else {
                        $url .= '/' . $this->urlencode($value);
                    }
                }
            }

            $url = rtrim($url, '/').'/'; // always use a trailing slash but never more

            // append all forced query string bits
            if(!empty($this->query_string_bits)){
                $url .= '?'.http_build_query($this->query_string_bits);
            }

        }

        $url = config()->get('lowercase_urls') ? strtolower($url) : $url;

        if($r['interface'] == "app" || $r['interface'] == ""){
            $url = str_replace("_app", "", $url);
        }

        if( $this->hash ){
            $url .= '#' . $this->hash;
        }

        $this->url = $url;

    }


    /**
     *
     */
    protected function urlencode($value){
        if($this->urlencode){
            return urlencode($value);
        } else {
            return $value;
        }
    }


    /**
     *
     * @return type
     */
    public function getUrl(){
        $this->buildUrl();
        return $this->url;
    }


    /**
     *
     * @return type
     */
    public function __toString(){
        return Link::encodeTextEntities( $this->getUrl() );
    }
}