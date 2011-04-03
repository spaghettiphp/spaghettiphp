<?php

class Hookable {
    protected $actions = array();
    protected $filters = array();

    public function registerHook($type, $hook, $method) {
        if(!array_key_exists($hook, $this->{$type})) {
            $this->{$type}[$hook] = array();
        }

        $this->{$type}[$hook] []= $method;
    }

    public function fireAction($hook, $params = array()) {
        $hook = $this->getHook($hook, 'actions');

        foreach($hook as $method) {
            $this->callHook($method, $params);
        }

        return true;
    }

    public function fireFilter($hook, $param) {
        $hook = $this->getHook($hook, 'filters');
        foreach($hook as $method) {
            $param = $this->callHook($method, array($param));
            if(!$param) {
                break;
            }
        }

        return $param;
    }

    protected function getHook($name, $type) {
        if(is_string($name)) {
            $hooks = array();

            if(array_key_exists($name, $this->{$type})) {
                $hooks = $this->{$type}[$name];
            }

            if(property_exists($this, $name)) {
                $hooks = array_merge($hooks, $this->{$name});
            }

            return $hooks;
        }
        else {
            return $name;
        }
    }

    protected function callHook($method, $params) {
        if(!is_callable($method)) {
            $method = array($this, $method);
        }

        return call_user_func_array($method, $params);
    }
}