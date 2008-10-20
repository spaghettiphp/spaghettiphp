<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Controller
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
    
class Controller extends Object {
    public $auto_layout = true;
    public $auto_render = true;
    public $components = array();
    public $data = array();
    public $helpers = array("Html");
    public $layout = "default";
    public $name = null;
    public $output = "";
    public $page_title = "";
    public $params = array();
    public $uses = null;
    public $view_data = array();
    public function __construct() {
        if($this->name === null && preg_match("/(.*)Controller/", get_class($this), $name)):
            if($name[1] && $name[1] != "App"):
                $this->name = $name[1];
            elseif($this->uses === null):
                $this->uses = array();
            endif;
        endif;
        if($this->uses === null):
            $this->uses = array($this->name);
        endif;
        $this->data = $_POST;
        $this->load_components();
        $this->load_models();
    }
    public function load_models() {
        foreach($this->uses as $model):
            $this->{$model} =& ClassRegistry::init($model);
        endforeach;
    }
    public function load_components() {
        #=>TODO: Load components through Component::load or something else
        foreach($this->components as $component):
            $component = "{$component}Component";
            $this->{$component} =& ClassRegistry::init($component, "Component");
        endforeach;
    }
    public function before_filter() {
        return true;
    }
    public function before_render() {
        return true;
    }
    public function after_filter() {
        return true;
    }
    public function set_action($action) {
        $this->params["action"] = $action;
        $args = func_get_args();
        unset($args[0]);
        call_user_func_array(array(&$this, $action), $args);
    }
    public function render($action = null, $layout = null) {
        $this->before_render();
        $view = new View($this);
        $view->set($this->view_data);
        $this->output .= $view->render($action, $layout);
        $this->auto_render = false;
        return $this->output;
    }
    public function clear() {
        $this->output = "";
        return true;
    }
    public function redirect($url = "", $status = null, $exit = true) {
        $this->auto_render = false;
        $codes = array(
            100 => "Continue",
            101 => "Switching Protocols",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            300 => "Multiple Choices",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            305 => "Use Proxy",
            307 => "Temporary Redirect",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Time-out",
            409 => "Conflict",
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Request Entity Too Large",
            414 => "Request-URI Too Large",
            415 => "Unsupported Media Type",
            416 => "Requested range not satisfiable",
            417 => "Expectation Failed",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Time-out"
        );
        if($status !== null && isset($codes[$status])):
            header("HTTP/1.1 {$status} {$codes[$status]}");
        endif;
        header("Location: " . Mapper::url($url, true));
        if($exit) die();
    }
    public function set($var = null, $content = null) {
        if(is_array($var)):
            foreach($var as $key => $value):
                $this->set($key, $value);
            endforeach;
            return true;
        elseif($var !== null):
            $this->view_data[$var] = $content;
            return $this->view_data[$var];
        endif;
        return false;
    }
    public function params($param = null) {
        return $this->params[$param];
    }
}

?>