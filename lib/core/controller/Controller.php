<?php

class Controller {
    public $autoLayout = true;
    public $autoRender = true;
    public $components = array();
    public $data = array();
    public $layout = 'default';
    public $name = null;
    public $params = array();
    public $uses = null;
    public $view = array();
    public $viewClass = 'View';

    public function __construct() {
        if(is_null($this->name)):
            $this->name = $this->name();
            
            // prevent models from being loaded in AppController
            if(is_null($this->uses) && $this->name == 'App'):
                $this->uses = array();
            endif;
        endif;
        
        if(is_null($this->uses)):
            $this->uses = array($this->name);
        endif;
        
        array_map(array($this, 'loadModel'), $this->uses);
        array_map(array($this, 'loadComponent'), $this->components);
        $this->data = array_merge_recursive($_POST, $_FILES);
    }
    public function name() {
        $classname = get_class($this);
        $lenght = strpos($classname, 'Controller');
        
        return substr($classname, 0, $lenght);
    }
    public function hasAction($action) {
        $methods = $this->getMethods();
        return in_array($action, $methods) && can_call_method($this, $action);
    }
    protected function getMethods() {
        $child = get_class_methods($this);
        $parent = get_class_methods('Controller');

        return array_diff($child, $parent);
    }
    public function callAction($request) {
        $this->params = $request;
        $this->componentEvent('initialize');
        $this->beforeFilter();
        $this->componentEvent('startup');
        
        if($this->hasAction($request['action'])):
            $params = $request['params'];
            if(!is_null($request['id'])):
                array_unshift($params, $request['id']);
            endif;
            call_user_func_array(array($this, $request['action']), $params);
        endif;

        $output = '';
        if($this->autoRender):
            $this->beforeRender();
            $output = $this->render($request['controller'] . '/' . $request['action'] . '.' . $request['extension']);
        endif;

        $this->componentEvent('shutdown');
        $this->afterFilter();
        
        return $output;
    }
    protected function loadModel($model) {
        $model = Inflector::camelize($model);
        if(Loader::exists('Model', $model)):
            $this->{$model} = Loader::instance('Model', $model);
        else:
            throw new MissingModelException(array(
                'model' => $model
            ));
        endif;
    }
    protected function loadComponent($component) {
        $component = Inflector::camelize($component) . 'Component';
        if(Loader::exists('Component', $component)):
            $this->{$component} = Loader::instance('Component', $component);
        else:
            throw new MissingComponentException(array(
                'component' => $component
            ));
        endif;
    }
    protected function componentEvent($event) {
        foreach($this->components as $component):
            $className = $component . 'Component';
            if(can_call_method($this->$className, $event)):
                $this->$className->{$event}($this);
            else:
                // @todo should throw exception
                trigger_error('Can\'t call method ' . $event . ' in ' . $className, E_USER_WARNING);
            endif;
        endforeach;
    }
    protected function beforeFilter() { }
    protected function beforeRender() { }
    protected function afterFilter() { }
    public function setAction($action) {
        $this->params['action'] = $action;
        $args = func_get_args();
        array_shift($args);
        return call_user_func_array(array($this, $action), $args);
    }
    public function render($action = null) {
        $view = new $this->viewClass;
        $view->controller = $this;
        $layout = $this->autoLayout ? $this->layout : false;
        $view->controller = $this;
        
        if(is_null($action)):
            $action = Inflector::underscore($this->name) . '/' . $this->params['action'];
        endif;

        $this->autoRender = false;
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
        if(is_null($value)):
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
        endif;
        
        return null;
    }
    public function param($key, $default = null) {
        if(array_key_exists($key, $this->params['named'])):
            return $this->params['named'][$key];
        elseif(in_array($key, array_keys($this->params))):
            return $this->params[$key];
        endif;
        
        return $default;
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