<?php

class Behavior {
    protected $model;
    protected $hooks = array();
    
    public function __construct($model) {
        $this->model = $model;
        $this->registerHook($this->hooks);
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
    public function registerHook($name, $method = null) {
        if(is_array($name)):
            foreach($name as $hook => $method):
                $this->registerHook($hook, $method);
            endforeach;
        elseif(is_array($method)):
            foreach($method as $m):
                $this->registerHook($name, $m);
            endforeach;
        else:
            $this->model->registerHook($name, array($this, $method));
        endif;
    }
}