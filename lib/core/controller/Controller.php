<?php

class Controller extends Object {
    public $autoRender = true;
    public $components = array();
    public $data = array();
    public $layout = 'default';
    public $name = null;
    public $params = array();
    public $uses = null;
    public $view = array();
    public $methods = array();
    public $viewClass = 'View';

    public function __construct() {
        if(is_null($this->name) && preg_match('/(.*)Controller/', get_class($this), $name)):
            if($name[1] && $name[1] != 'App'):
                $this->name = $name[1];
            elseif(is_null($this->uses)):
                $this->uses = array();
            endif;
        endif;
        if(is_null($this->uses)):
            $this->uses = array($this->name);
        endif;
        
        $this->methods = $this->getMethods();
        $this->data = array_merge_recursive($_POST, $_FILES);
        $this->loadComponents();
        $this->loadModels();
    }
    public function __get($class){
        if(!isset($this->{$class})):
            $pattern = '(^[A-Z]+([a-z]+(Component)?))';
            if(preg_match($pattern, $class, $out)):
                $type = (isset($out[2])) ? 'Component' : 'Model';
                $this->{$class} = ClassRegistry::load($class, $type);
                if($type == 'Component') $this->{$class}->initialize($this);
                return $this->{$class};
            endif;
        endif;
    }
    public function getMethods() {
        $child = get_class_methods($this);
        $parent = get_class_methods('Controller');
        return array_diff($child, $parent);
    }
    public function loadComponents() {
        foreach($this->components as $component):
            $component = $component . 'Component';
            if(Loader::exists('Component', $component)):
                $this->{$component} = Loader::instance('Component', $component);
            else:
                $this->error('missingComponent', array('component' => $component));
            endif;
        endforeach;
        return true;
    }
    public function componentEvent($event) {
        foreach($this->components as $component):
            $className = $component . 'Component';
            if(can_call_method($this->$className, $event)):
                $this->$className->{$event}($this);
            else:
                trigger_error('Can\'t call method ' . $event . ' in ' . $className, E_USER_WARNING);
            endif;
        endforeach;
    }
    public function loadModels() {
        foreach($this->uses as $model):
            if(!$this->{$model} = ClassRegistry::load($model)):
                $this->error('missingModel', array('model' => $model));
                return false;
            endif;
        endforeach;
        return true;
    }
    public function beforeFilter() {
        return true;
    }
    public function beforeRender() {
        return true;
    }
    public function afterFilter() {
        return true;
    }
    public function setAction($action) {
        $this->params['action'] = $action;
        $args = func_get_args();
        array_shift($args);
        return call_user_func_array(array(&$this, $action), $args);
    }
    public function render($action = null) {
        $view = new $this->viewClass;
        $view->layout = $this->layout;
        
        if(is_null($action)):
            $action = Inflector::underscore($this->name) . '/' . $this->params['action'];
        endif;

        $this->autoRender = false;

        return $view->render($action, $this->view);
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
        elseif(!is_null($value)):
            $this->view[$var] = $value;
        endif;
        
        return $this;
    }
    public function get($var) {
        if(isset($this->view[$var])):
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
}