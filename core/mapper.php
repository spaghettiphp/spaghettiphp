<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Mapper
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Mapper extends Object {
    public $prefixes = array();
    public $routes = array();
    public function &get_instance() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] =& new Mapper();
        endif;
        return $instance[0];
    }
    public function url($path = null, $full = false) {
        if(preg_match("/^[a-z]*:\/\//", $path)):
            return $path;
        endif;
        $url = WEBROOT . trim($path, "/");
        return $full ? HOST . $url : $url;
    }
    public function connect($url = "", $route = array()) {
        $self = Mapper::get_instance();
        $url = rtrim($url, "/");
        return $self->routes[$url] = rtrim($route, "/");
    }
    public function disconnect($url = "") {
        $self = Mapper::get_instance();
        $url = rtrim($url, "/");
        unset($self->routes[$url]);
        return true;
    }
    public function get_route($url) {
        $self = Mapper::get_instance();
  
        foreach($self->routes as $map => $route):
            $map = "/^" . str_replace(array("/", ":any", ":part", ":num"), array("\/", "(.*)", "([^\/]*)", "([0-9]+)"), $map) . "\/?$/";
            $url = preg_replace($map, $route, $url);
        endforeach;
  
          return rtrim($url, "/");
    }
    public function prefix($prefix = "") {
        $self = Mapper::get_instance();
        return $self->prefixes []= $prefix;
    }
    public function unset_prefix($prefix = "") {
        $self = Mapper::get_instance();
        unset($self->prefixes[$prefix]);
        return true;
    }
    public function get_prefixes() {
        $self = Mapper::get_instance();
        return $self->prefixes;
    }
}

?>