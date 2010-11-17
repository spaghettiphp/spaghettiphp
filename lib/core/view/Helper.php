<?php

class Helper {
    protected $view;
    
    public function __construct($view) {
        $this->view = $view;
    }
    public function __get($helper) {
        return $this->view->{$helper};
    }
    public static function load($name) {
        if(!class_exists($name) && Filesystem::exists('lib/helpers/' . $name . '.php')):
            require_once 'lib/helpers/' . $name . '.php';
        endif;
        if(!class_exists($name)):
            $message = 'The helper <code>' . $name . '</code> was not found.';
            throw new RuntimeException('The helper <code>' . $name . '</code> was not found.');
        endif;        
    }
}