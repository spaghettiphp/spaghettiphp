<?php

abstract class Helper extends Object {
    protected $view;
    
    public function __construct($view) {
        $this->view = $view;
    }
}