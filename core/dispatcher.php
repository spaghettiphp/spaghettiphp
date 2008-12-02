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
        $this->parse_url($url);
        if($dispatch) return $this->dispatch();
    }
    /**
     * O método Dispatcher::parse_url() faz o parse da URL, identificando o prefixo,
     * controller, action, id, extensão e parâmetros nesta URL.
     *
     * @param string $url URL a ser parseadad
     * @return void
     */
    public function parse_url($url = null) {
        if($url === null) $url = Mapper::here();
        $this->url = $url;
        $url = Mapper::get_route($this->url);
        $prefixes = join("|", Mapper::get_prefixes());
        
        $parts = array("here", "prefix", "controller", "action", "id", "extension", "params");
        preg_match("/^\/(?:({$prefixes})(?:\/|(?!\w)))?(?:(\w*)\/?)?(?:([a-z_-]*)\/?)?(?:(\d*))?(?:\.([\w]+))?(?:\/?(.*))?/", $url, $reg);
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
    /**
     * Dispatcher::dispatch() é o método que chama o controller e a action
     * solicitada.
     *
     * @return void
  */ 
    public function dispatch() {
        $controllerName = Inflector::camelize("{$this->path['controller']}_controller");
        $controller =& ClassRegistry::init($controllerName, "Controller");
        $action = Inflector::camelize($this->path["action"]);
        if($controller && method_exists($controller, $action)):
            $controller->params = $this->path;
            $controller->Component->initialize($controller);
            $controller->before_filter();
            $controller->Component->startup($controller);
            call_user_func_array(array(&$controller, $action), array_merge(array($this->path["id"]), $this->path["params"]));
            if($controller->auto_render):
                $controller->render();
            endif;
            $controller->Component->shutdown($controller);
            echo $controller->output;
            $controller->after_filter();
        elseif(Spaghetti::import("View", "{$this->path['controller']}/{$this->path['action']}", "p{$this->path['extension']}", true)):
            if(!$controller) $controller =& new Controller;
            $controller->params = $this->path;
            echo $controller->render();
        else:
            $this->error("missingAction", array("controller" => $controllerName, "action" => $action));
        endif;
    }
}

?>