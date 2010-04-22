<?php

abstract class Helper extends Object {
    protected $view;
    
    public function __construct($view) {
        $this->view = $view;
    }
    public function __get($helper) {
        return $this->view->{$helper};
    }
}