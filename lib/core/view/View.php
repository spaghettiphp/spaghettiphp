<?php

class View extends Object {
    public $extension = 'htm';
    public $contentForLayout;
    public $scriptsForLayout;
    public $stylesForLayout;
    public $helpers = array('html', 'form');
    public $controller;
    protected $loadedHelpers = array();
    protected $blocks = array();
    protected $lastBlock;

    public function __construct() {
        $this->loadHelper($this->helpers);
    }
    public function __get($name) {
        if(!array_key_exists($name, $this->loadedHelpers)):
            $this->loadHelper($name);
        endif;
        
        return $this->loadedHelpers[$name];
    }
    public function loadHelper($helper) {
        if(is_array($helper)):
            return array_walk($helper, array($this, 'loadHelper'));
        endif;

        $helper_class = Inflector::camelize($helper) . 'Helper';
        require_once 'lib/helpers/' . $helper_class . '.php';
        $this->loadedHelpers[$helper] = new $helper_class($this);
    }
    public function render($action, $data = array(), $layout = false) {
        $filename = explode('.', $action);
        $action = $filename[0];
        if(array_key_exists(1, $filename)):
            $extension = $filename[1];
        else:
            $extension = $this->extension;
        endif;
        $view_file = Loader::path('View', $action . '.' . $extension);
        
        if(file_exists($view_file)):
            $output = $this->renderView($view_file, $data);
            if($layout):
                $output = $this->renderLayout($layout, $output, $data);
            endif;
            return $output;
        else:
            $this->error('missingView', array(
                'view' => $action,
                'extension' => $extension
            ));
            return false;
        endif;
    }
    public function renderLayout($layout, $content, $data) {
        $layout_file = Loader::path('Layout', $layout . '.' . $this->extension);
        if(file_exists($layout_file)):
            $this->contentForLayout = $content;
            return $this->renderView($layout_file, $data);
        else:
            $this->error('missingLayout', array(
                'layout' => $layout
            ));
            return false;
        endif;        
    }
    public function element($element, $data = array()) {
        $element = dirname($element) . '/_' . basename($element);
        $element_path = Loader::path('View', $element . '.' . $this->extension);
        return $this->renderView($element_path, $data);
    }
    protected function renderView($filename, $data = array()) {
        extract($data, EXTR_OVERWRITE);
        ob_start();
        require $filename;
        $output = ob_get_clean();
        return $output;
    }
    
    // @todo under testing
    public function startBlock($name) {
        $this->lastBlock = $name;
        ob_start();
    }
    public function endBlock() {
        return $this->blocks[$this->lastBlock] = ob_get_clean();
    }
    public function block($name) {
        return $this->blocks[$name];
    }
}