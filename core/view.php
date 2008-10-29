<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.View
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
    
class View extends Object {
    public $helpers = array("Html");
    public $loaded_helpers = array();
    public $controller;
    public $action;
    public $extension;
    public $layout;
    public $page_title;
    public $auto_layout = true;
    public $view_data = array();
    public function __construct(&$controller = null) {
        if($controller):
            $this->controller = $controller->params("controller");
            $this->action = $controller->params("action");
            $this->extension = $controller->params("extension");
            $this->page_title = $controller->page_title;
            $this->layout = $controller->layout;
            $this->auto_layout = $controller->auto_layout;
        endif;
    }
    public function load_helpers() {
        foreach($this->helpers as $helper):
            $class = "{$helper}Helper";
            $this->loaded_helpers[Inflector::underscore($helper)] = ClassRegistry::init($class, "Helper");
        endforeach;
        return $this->loaded_helpers;
    }
    public function render_view($filename = null, $extract_vars = array()) {
        if(!is_string($filename)):
            return false;
        endif;
        if(empty($this->loaded_helpers) && !empty($this->helpers)):
            $this->load_helpers();
        endif;
        $extract_vars = is_array($extract_vars) ? array_merge($extract_vars, $this->loaded_helpers) : $this->loaded_helpers;
        extract($extract_vars, EXTR_SKIP);
        ob_start();
        include $filename;
        $out = ob_get_clean();
        return $out;
    }
    public function render($action = null, $layout = null) {
        if($action === null):
            $action = "{$this->controller}/{$this->action}";
            $ext = "p{$this->extension}";
        else:
            $filename = preg_split("/\./", $action);
            $action = $filename[0];
            $ext = $filename[1] ? $filename[1] : "phtm";
        endif;
        $layout = $layout === null ? "{$this->layout}.{$ext}" : $layout;
        $filename = Spaghetti::import("View", $action, $ext, true);
        if($filename):
            $out = $this->render_view($filename, $this->view_data);
            if($layout && $this->auto_layout):
                $out = $this->render_layout($out, $layout);
            endif;
            return $out;
        else:
            $this->error("missingView", array("controller" => $this->controller, "view" => $action, "extension" => $ext));
            return false;
        endif;
    }
    public function render_layout($content = null, $layout = null) {
        if($layout === null):
            $layout = $this->layout;
            $ext = "p{$this->extension}";
        else:
            $filename = preg_split("/\./", $layout);
            $layout = $filename[0];
            $ext = $filename[1] ? $filename[1] : "phtm";
        endif;
        $filename = Spaghetti::import("Layout", $layout, $ext, true);
        $data = array_merge(array(
            "content_for_layout" => $content,
            "page_title" => $this->page_title,
        ), $this->view_data);
        if($filename):
            $out = $this->render_view($filename, $data);
            return $out;
        else:
            $this->error("missingLayout", array("layout" => $layout, "extension" => $ext));
            return false;
        endif;
    }
    public function element($element = null, $params = array()) {
        $ext = $this->extension ? $this->extension : "phtm";
        return $this->render_view(Spaghetti::import("View", "_{$element}", $ext, true), $params);
    }
    public function set($var = null, $content = null) {
        if(is_array($var)):
            foreach($var as $key => $value):
                $this->set($key, $value);
            endforeach;
        elseif($var !== null):
            $this->view_data[$var] = $content;
            return $this->view_data[$var];
        endif;
        return false;
    }
}

?>