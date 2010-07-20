<?php

class Behavior {
    protected $model;
    protected $options;
    protected $actions = array();
    protected $filters = array();
    
    public function __construct($model, $options = array()) {
        $this->model = $model;
        $this->options = $options;
        $this->registerAction($this->actions);
        $this->registerFilter($this->filters);
    }
    public function hasMethod($method) {
        $class = new ReflectionClass(get_class($this));
        if($class->hasMethod($method)):
            $m = $class->getMethod($method);
            return $m->class != 'Behavior' && $m->isPublic();
        else:
            return false;
        endif;
    }
    protected function register($type, $name, $method = null) {
        if(is_array($name)):
            foreach($name as $hook => $method):
                $this->register($type, $hook, $method);
            endforeach;
        elseif(is_array($method)):
            foreach($method as $m):
                $this->register($type, $name, $m);
            endforeach;
        else:
            $this->model->register($type, $name, array($this, $method));
        endif;
    }
    public function registerAction($name, $method = null) {
        $this->register('actions', $name, $method);
    }
    public function registerFilter($name, $method = null) {
        $this->register('filters', $name, $method);
    }
}