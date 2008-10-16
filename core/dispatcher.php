<?php
/**
 *  The Spaghetti.Dispatcher class is responsible for getting the
 *  querystring parameters from URL, parsing them, figuring out
 *  their meanings and including the necessary files, as well as
 *  calling and rendering the Controller.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Dispatcher
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

class Dispatcher extends Object {
    public $path = array();
    public $url = "";
    public function __construct($dispatch = true) {
        $_GET["spaghetti_querystring"] = isset($_GET["spaghetti_querystring"]) ? $_GET["spaghetti_querystring"] : "";
        $this->parse_url();
        if($dispatch) return $this->dispatch();
    }
    public function parse_url($url = null) {
        if($url === null) $url = isset($_GET["spaghetti_querystring"]) ? $_GET["spaghetti_querystring"] : "";
        $this->url = "/" . trim($url, "/");
        $url = Mapper::get_route($this->url);
        $prefixes = join("|", Mapper::get_prefixes());
        
        $parts = array("here", "prefix", "controller", "action", "id", "extension", "params");
        preg_match("/^\/(?:({$prefixes})(?:\/|(?!\w)))?(?:(\w*)\/?)?(?:(\w*)\/?)?(?:(\d)*)?(?:\.([\w]+))?(?:\/?(.*))?/", $url, $reg);
        #preg_match("/^\/(?:({$prefixes})\/?(?![a-z]))?([^\d\/.:]*)?\/?([^\d\/.:]*)?\/?(\d*)?(?:\.([^.\/]{2,4}))?\/?(.*)/", $url, $reg);
        foreach($parts as $k => $key) {
            $this->path[$key] = $reg[$k];
        }
        
        if($this->path["action"] == "") $this->path["action"] = "index";
        if($this->path["prefix"] != "") $this->path["action"] = "{$this->path['prefix']}_{$this->path['action']}";
        if($this->path["params"] != "") $this->path["params"] = split("/", $this->path["params"]);
        else $this->path["params"] = array();
        if($this->path["extension"] == "") $this->path["extension"] = Config::read("default_extension");
        $this->path["here"] = $this->url;

        return $this->path;
    }
    public function dispatch() {
        if(in_array($this->path["controller"], Config::read("filters"))):
            $this->dispatch_filter();
            return true;
        endif;
        
        $controller = Inflector::camelize("{$this->path['controller']}_controller");
        $this->{$controller} =& ClassRegistry::get_object("Controller", $controller);

        if($this->{$controller}):
            if(method_exists($this->{$controller}, $this->path["action"])):
                $this->{$controller}->params = $this->path;
                $this->{$controller}->before_filter();
                call_user_func_array(array(&$this->{$controller}, $this->path["action"]), array_merge(array($this->path["id"]), $this->path["params"]));

                if($this->{$controller}->auto_render):
                    $this->{$controller}->render();
                endif;
                echo $this->{$controller}->output;
                
                $this->{$controller}->after_filter();
                
            elseif(Spaghetti::import("View", "{$this->path['controller']}/{$this->path['action']}", "p{$this->path['extension']}", true)):
                $this->dispatch_view();
                
            else:
                $this->error("missingAction", array("controller" => $controller, "action" => $this->path["action"]));
            endif;

        elseif(Spaghetti::import("View", "{$this->path['controller']}/{$this->path['action']}", "p{$this->path['extension']}", true)):
            $this->dispatch_view();
        endif;
    }
    public function dispatch_view() {
        $view = new View;
        echo $view->render("{$this->path['controller']}/{$this->path['action']}.p{$this->path['extension']}", false);
    }
    public function dispatch_filter() {
        $filter =& ClassRegistry::get_object("Filter", Inflector::camelize("{$this->path['controller']}_filter"));
        if($filter):
            preg_match("/{$this->path['controller']}\/?(.*)/", $this->url, $file);
            $filter->start($file[1]);
        endif;
    }
}

?>