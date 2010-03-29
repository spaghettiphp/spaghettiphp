<?php

class Mapper extends Object {
    public $prefixes = array();
    public $routes = array();
    private $here = null;
    private $base = null;
    public $root = null;

    public function __construct() {
        if(!$this->base):
            $this->base = dirname($_SERVER['PHP_SELF']);
            if(basename($this->base) == 'public'):
                $this->base = dirname($this->base);
                if($this->base == DIRECTORY_SEPARATOR || $this->base == '.'):
                    $this->base = '/';
                endif;
            endif;
        endif;
        if(!$this->here):
            $start = strlen($this->base);
            $this->here = self::normalize(substr($_SERVER['REQUEST_URI'], $start));
        endif;
    }
    public static function &getInstance() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] = new Mapper();
        endif;
        return $instance[0];
    }
    public static function here() {
        $self = self::getInstance();
        return $self->here;
    }
    public static function base() {
        $self = self::getInstance();
        return $self->base;
    }
    public static function normalize($url) {
        if(preg_match('/^[a-z]+:/', $url)):
            return $url;
        endif;
        $url = '/' . $url;
        while(strpos($url, '//') !== false):
            $url = str_replace('//', '/', $url);
        endwhile;
        $url = rtrim($url, '/');
        if(empty($url)):
            $url = '/';
        endif;
        return $url;
    }
    public static function root($controller) {
        $self = self::getInstance();
        $self->root = $controller;
        return true;
    }
    public static function getRoot() {
        $self = self::getInstance();
        return $self->root;
    }
    public static function prefix($prefix) {
        $self = self::getInstance();
        if(is_array($prefix)) $prefixes = $prefix;
        else $prefixes = func_get_args();
        foreach($prefixes as $prefix):
            $self->prefixes []= $prefix;
        endforeach;
        return true;
    }
    public static function unsetPrefix($prefix) {
        $self = self::getInstance();
        unset($self->prefixes[$prefix]);
        return true;
    }
    public static function getPrefixes() {
        $self = self::getInstance();
        return $self->prefixes;
    }
    public static function connect($url = null, $route = null) {
        if(is_array($url)):
            foreach($url as $key => $value):
                self::connect($key, $value);
            endforeach;
        elseif(!is_null($url)):
            $self = self::getInstance();
            $url = self::normalize($url);
            $self->routes[$url] = rtrim($route, '/');
        endif;
        return true;
    }
    public static function disconnect($url) {
        $self = self::getInstance();
        $url = rtrim($url, '/');
        unset($self->routes[$url]);
        return true;
    }
    public static function match($check, $url = null) {
        if(is_null($url)):
            $url = self::here();
        endif;
        $check = '%^' . str_replace(array(':any', ':fragment', ':num'), array('(.+)', '([^\/]+)', '([0-9]+)'), $check) . '/?$%';
        return preg_match($check, $url);
    }
    public static function getRoute($url) {
        $self = self::getInstance();
        foreach($self->routes as $map => $route):
            if(self::match($map, $url)):
                $map = '%^' . str_replace(array(':any', ':fragment', ':num'), array('(.+)', '([^\/]+)', '([0-9]+)'), $map) . '/?$%';
                $url = preg_replace($map, $route, $url);
                break;
            endif;
        endforeach;
        return self::normalize($url);
    }
    public static function parse($url = null) {
        $here = self::normalize(is_null($url) ? self::here() : $url);
        $url = self::getRoute($here);
        $prefixes = join('|', self::getPrefixes());
        
        $path = array();
        $parts = array('here', 'prefix', 'controller', 'action', 'id', 'extension', 'params', 'queryString');
        preg_match('/^\/(?:(' . $prefixes . ')(?:\/|(?!\w)))?(?:([a-z_-]*)\/?)?(?:([a-z_-]*)\/?)?(?:(\d*))?(?:\.([\w]+))?(?:\/?([^?]+))?(?:\?(.*))?/i', $url, $reg);
        foreach($parts as $k => $key):
            $path[$key] = isset($reg[$k]) ? $reg[$k] : null;
        endforeach;
        
        $path['named'] = $path['params'] = array();
        if(isset($reg[6])):
            foreach(explode('/', $reg[6]) as $param):
                if(preg_match('/([^:]*):([^:]*)/', $param, $reg)):
                    $path['named'][$reg[1]] = urldecode($reg[2]);
                elseif($param != ''):
                    $path['params'] []= urldecode($param);
                endif;
            endforeach;
        endif;

        $path['here'] = $here;
        if(empty($path['controller'])) $path['controller'] = self::getRoot();
        if(empty($path['action'])) $path['action'] = 'index';
        if($filtered = self::filterAction($path['action'])):
            $path['prefix'] = $filtered['prefix'];
            $path['action'] = $filtered['action'];
        endif;
        if(!empty($path['prefix'])):
            $path['action'] = $path['prefix'] . '_' . $path['action'];
        endif;
        if(empty($path['id'])) $path['id'] = null;
        if(empty($path['extension'])) $path['extension'] = Config::read('App.defaultExtension');
        if(!empty($path['queryString'])):
            parse_str($path['queryString'], $queryString);
            $path['named'] = array_merge($path['named'], $queryString);
        endif;
        
        return $path;
    }
    public static function url($path, $full = false) {
        if(is_array($path)):
            $here = self::parse();
            $params = $here['named'];
            $path = array_merge(array(
                'prefix' => $here['prefix'],
                'controller' => $here['controller'],
                'action' => $here['action'],
                'id' => $here['id'],
                'params' => $here['params']
            ), $params, $path);
            $nonParams = array('prefix', 'controller', 'action', 'id', 'params');
            $url = '';
            foreach($path as $key => $value):
                if(!in_array($key, $nonParams)):
                    $url .= '/' . $key . ':' . $value;
                elseif(!is_null($value)):
                    if($key == 'action' && $filtered = self::filterAction($value)):
                        $value = $filtered['action'];
                    elseif($key == 'params'):
                        $value = join('/', $value);
                    endif;
                    $url .= '/' . $value;
                endif;
            endforeach;
        else:
            if(preg_match('/^[a-z]+:/', $path)):
                return $path;
            elseif(substr($path, 0, 1) == '/'):
                $url = $path;
            else:
                if(substr($path, 0, 1) != '#'):
                    $path = '/' . $path;
                endif;
                $url = self::here() . $path;
            endif;
        endif;
        $url = self::normalize(self::base() . $url);
        return $full ? BASE_URL . $url : $url;
    }
    public static function filterAction($action) {
        if(strpos($action, '_') !== false):
            foreach(self::getPrefixes() as $prefix):
                if(strpos($action, $prefix) === 0):
                    return array(
                        'action' => substr($action, strlen($prefix) + 1),
                        'prefix' => $prefix
                    );
                endif;
            endforeach;
        endif;
        return false;
    }
}