<?php

require 'lib/core/controller/Component.php';
require 'lib/core/controller/Exceptions.php';

class Controller {
    protected $autoLayout = true;
    protected $autoRender = true;
    protected $components = array();
    protected $data = array();
    protected $layout = 'default';
    protected $name = null;
    protected $params = array();
    protected $uses = null;
    protected $view = array();

    public function __construct() {
        if(is_null($this->name)):
            $this->name = $this->name();
        endif;

        if(is_null($this->uses)):
            if($this->name == 'App'):
                $this->uses = array();
            else:
                $this->uses = array($this->name);
            endif;
        endif;
        
        array_map(array($this, 'loadModel'), $this->uses);
        array_map(array($this, 'loadComponent'), $this->components);
        $this->data = array_merge_recursive($_POST, $_FILES);
    }
    public static function load($name, $instance = false) {
        if(!class_exists($name) && Filesystem::exists('app/controllers/' . Inflector::underscore($name) . '.php')):
            require_once 'app/controllers/' . Inflector::underscore($name) . '.php';
        endif;
        if(class_exists($name)):
            if($instance):
                return new $name();
            else:
                return true;
            endif;
        else:
            throw new MissingControllerException(array(
                'controller' => $name
            ));
        endif;
    }
    public static function hasViewForAction($request) {
        return Loader::exists('View', $request['controller'] . '/' . $request['action'] . '.' . $request['extension']);
    }
    public function name() {
        $classname = get_class($this);
        $lenght = strpos($classname, 'Controller');
        
        return substr($classname, 0, $lenght);
    }
    public function callAction($request) {
        if($this->hasAction($request['action']) || Controller::hasViewForAction($request)):
            return $this->dispatch($request);
        else:
            throw new MissingActionException(array(
                'controller' => $request['controller'],
                'action' => $request['action']
            ));
        endif;
    }
    public function hasAction($action) {
        $class = new ReflectionClass(get_class($this));
        if($class->hasMethod($action)):
            $method = $class->getMethod($action);
            return $method->class != 'Controller' && $method->isPublic();
        else:
            return false;
        endif;
    }
    protected function dispatch($request) {
        $this->params = $request;
        $this->componentEvent('initialize');
        $this->beforeFilter();
        $this->componentEvent('startup');
    
        if($this->hasAction($request['action'])):
            call_user_func_array(array($this, $request['action']), $request['params']);
        endif;

        $output = '';
        if($this->autoRender):
            $this->beforeRender();
            $output = $this->render();
        endif;

        $this->componentEvent('shutdown');
        $this->afterFilter();
    
        return $output;
    }
    protected function loadModel($model) {
        $model = Inflector::camelize($model);
        return $this->{$model} = Model::load($model);
    }
    protected function loadComponent($component) {
        $component = Inflector::camelize($component) . 'Component';
        return $this->{$component} = Component::load($component, true);
    }
    protected function componentEvent($event) {
        foreach($this->components as $component):
            $className = $component . 'Component';
            $this->$className->{$event}($this);
        endforeach;
    }
    protected function beforeFilter() { }
    protected function beforeRender() { }
    protected function afterFilter() { }
    public function setAction($action) {
        $args = func_get_args();
        $this->params['action'] = array_shift($args);
        
        return call_user_func_array(array($this, $action), $args);
    }
    public function render($action = null) {
        $view = new View;
        $layout = $this->autoLayout ? $this->layout : false;
        $view->controller = $this;
        $this->autoRender = false;
        
        if(is_null($action)):
            $action = Inflector::underscore($this->name) . '/' . $this->params['action'] . '.' . $this->params['extension'];
        endif;

        return $view->render($action, $this->view, $layout);
    }
    public function redirect($url, $status = null, $exit = true) {
        $this->autoRender = false;
        $codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out'
        );
        if(!is_null($status) && isset($codes[$status])):
            header('HTTP/1.1 ' . $status . ' ' . $codes[$status]);
        endif;
        header('Location: ' . Mapper::url($url, true));
        if($exit) $this->stop();
    }
    public function set($var, $value = null) {
        if(is_array($var)):
            foreach($var as $key => $value):
                $this->set($key, $value);
            endforeach;
        else:
            $this->view[$var] = $value;            
        endif;
    }
    public function get($var) {
        if(array_key_exists($var, $this->view)):
            return $this->view[$var];
        else:
            return null;
        endif;
    }
    public function param($key, $default = null) {
        if(array_key_exists($key, $this->params['named'])):
            return $this->params['named'][$key];
            
        elseif(in_array($key, array_keys($this->params))):
            return $this->params[$key];
            
        else:
            return $default;
        endif;
    }
    public function page($param = 'page') {
        return $this->param($param, 1);
    }
    public function isXhr() {
        if(array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)):
            return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        endif;
        return false;
    }
    public function stop() {
        exit(0);
    }
}
