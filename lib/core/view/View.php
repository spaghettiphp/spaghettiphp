<?php

require 'lib/core/view/Helper.php';
require 'lib/core/view/Exceptions.php';

class View {
    public $pageTitle;
    public $contentForLayout;
    public $helpers = array('html', 'form');
    public $controller;
    protected $loadedHelpers = array();
    protected $blocks = array();
    protected $lastBlock;

    public function __construct() {
        array_map(array($this, 'loadHelper'), $this->helpers);
    }
    public function __get($name) {
        if(!array_key_exists($name, $this->loadedHelpers)):
            $this->loadHelper($name);
        endif;
        
        return $this->loadedHelpers[$name];
    }
    public function loadHelper($helper) {
        $helper_class = Inflector::camelize($helper) . 'Helper';
        Helper::load($helper_class);
        return $this->loadedHelpers[$helper] = new $helper_class($this);
    }
    public function render($action, $data = array(), $layout = false) {
        $view_file = Loader::path('View', $this->filename($action));
        
        if(Filesystem::exists($view_file)):
            $output = $this->renderView($view_file, $data);
            if($layout):
                $output = $this->renderLayout($layout, $output, $data);
            endif;
            return $output;
        else:
            throw new MissingViewException(array(
                'view' => $this->filename($action)
            ));
        endif;
    }
    public function renderLayout($layout, $content, $data) {
        $layout_file = Loader::path('Layout', $this->filename($layout));

        if(Filesystem::exists($layout_file)):
            $this->contentForLayout = $content;
            return $this->renderView($layout_file, $data);
        else:
            throw new MissingViewException(array(
                'view' => $this->filename($layout)
            ));
        endif;
    }
    public function element($element, $data = array()) {
        $element_path = Loader::path('View', $this->elementName($element));
        
        if(Filesystem::exists($element_path)):
            return $this->renderView($element_path, $data);
        else:
            throw new MissingViewException(array(
                'view' => $this->filename($element)
            ));
        endif;
    }
    public function renderView($filename, $data = array()) {
        extract($data);
        ob_start();
        require $filename;
        return ob_get_clean();
    }
    protected function filename($filename) {
        if(is_null(Filesystem::extension($filename))):
            $filename .= '.htm';
        endif;

        return $filename;
    }
    protected function elementName($element) {
        return dirname($element) . '/_' . $this->filename(basename($element));
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
    public static function path($request) {
        return $request['controller'] . '/' . $request['action'] . '.' . $request['extension'];
    }
}