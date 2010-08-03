<?php

class Hookable {
    protected $actions = array();
    protected $filters = array();

    public function register($type, $hook, $method) {
        if(!array_key_exists($hook, $this->{$type})):
            $this->{$type}[$hook] = array();
        endif;
        $this->{$type}[$hook] []= $method;
    }
    public function fireAction($hook, $params = array()) {
        if(array_key_exists($hook, $this->actions)):
            foreach($this->actions[$hook] as $method):
                $this->callHook($method, $params);
            endforeach;
        endif;
    }
    public function fireFilter($hook, $param) {
        if(array_key_exists($hook, $this->filters)):
            foreach($this->filters[$hook] as $method):
                $param = $this->callHook($method, array($param));
                if(!$param):
                    break;
                endif;
            endforeach;
        endif;

        return $param;
    }
    protected function callHook($method, $params) {
        if(!is_callable($method)):
            $method = array($this, $method);
        endif;
        
        return call_user_func_array($method, $params);
    }
}