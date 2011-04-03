<?php

class Behavior {
    protected $model;
    protected $options = array();
    protected $defaults = array();
    protected $actions = array();
    protected $filters = array();

    public function __construct($model, $options = array()) {
        $this->model = $model;
        $this->options = $this->options($options);
        $this->registerAction($this->actions);
        $this->registerFilter($this->filters);
    }

    public static function load($name) {
        $filename = 'lib/behaviors/' . $name . '.php';
        if(!class_exists($name) && Filesystem::exists($filename)) {
            require $filename;
        }
        if(!class_exists($name)) {
            throw new RuntimeException('The behavior <code>' . $name . '</code> was not found.');
        }
    }

    protected function options($options) {
        return array_merge($this->defaults, $options);
    }

    protected function register($type, $name, $method = null) {
        if(is_array($name)) {
            foreach($name as $hook => $method) {
                $this->register($type, $hook, $method);
            }
        }
        elseif(is_array($method)) {
            foreach($method as $m) {
                $this->register($type, $name, $m);
            }
        }
        else {
            $this->model->registerHook($type, $name, array($this, $method));
        }
    }

    public function registerAction($name, $method = null) {
        $this->register('actions', $name, $method);
    }

    public function registerFilter($name, $method = null) {
        $this->register('filters', $name, $method);
    }
}