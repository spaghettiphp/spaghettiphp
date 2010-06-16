<?php

class View {
    public $pageTitle = '';
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
        $view_file = Loader::path('View', $this->filename($action));
        
        if(file_exists($view_file)):
            $output = $this->renderView($view_file, $data);
            if($layout):
                $output = $this->renderLayout($layout, $output, $data);
            endif;
            return $output;
        else:
            throw new MissingViewException();
            // $this->error('missingView', array(
            //     'view' => $action
            // ));
            // return false;
        endif;
    }
    public function renderLayout($layout, $content, $data) {
        $layout_file = Loader::path('Layout', $this->filename($layout));

        if(file_exists($layout_file)):
            $this->contentForLayout = $content;
            return $this->renderView($layout_file, $data);
        else:
            throw new MissingLayoutException();
            // $this->error('missingLayout', array(
            //     'layout' => $layout
            // ));
            // return false;
        endif;        
    }
    public function element($element, $data = array()) {
        $element = dirname($element) . '/_' . $this->filename(basename($element));
        $element_path = Loader::path('View', $element);
        return $this->renderView($element_path, $data);
    }
    public function renderView($filename, $data = array()) {
        extract($data);
        ob_start();
        require $filename;
        return ob_get_clean();
    }
    public function filename($filename) {
        if(is_null(Filesystem::extension($filename))):
            $filename .= '.htm';
        endif;

        return $filename;
    }
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