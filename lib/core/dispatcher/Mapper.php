<?php

/*
    Class: Mapper
*/
class Mapper {
    protected $prefixes = array();
    protected $routes = array();
    protected $base;
    protected $here;
    protected $domain;
    protected $root;
    protected static $instance;

    public static function instance() {
        if(!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public static function here() {
        $self = self::instance();

        if(is_null($self->here)) {
            if(array_key_exists('REQUEST_URI', $_SERVER)) {
                $start = strlen(self::base());
                $request_uri = substr($_SERVER['REQUEST_URI'], $start);
                $self->here = self::normalize($request_uri);
            }
            else {
                $self->here = '/';
            }
        }

        return $self->here;
    }

    public static function base() {
        $self = self::instance();

        if(is_null($self->base)) {
            $self->base = dirname($_SERVER['PHP_SELF']);

            if(basename($self->base) == 'public') {
                $self->base = dirname($self->base);
            }

            if($self->base == DIRECTORY_SEPARATOR || $self->base == '.') {
                $self->base = '/';
            }
        }

        return $self->base;
    }

    public static function domain() {
        $self = self::instance();

        if(is_null($self->domain)) {
            if(array_key_exists('REQUEST_URI', $_SERVER)) {
                $s = array_key_exists('HTTPS', $_SERVER) ? 's' : '';
                $self->domain = 'http' . $s . '://' . $_SERVER['HTTP_HOST'];
            }
            else {
                $self->domain = 'http://localhost';
            }
        }

        return $self->domain;
    }

    public static function normalize($url) {
        if(!self::isExternal($url)) {
            $url = preg_replace('%/+%', '/', $url);
            $url = '/' . trim($url, '/');
        }

        return $url;
    }

    public static function root($controller = null) {
        $self = self::instance();

        if(is_null($controller)) {
            return $self->root;
        }
        else {
            $self->root = $controller;
        }
    }

    public static function url($url, $full = false, $base = '/') {
        if(self::isExternal($url)) {
            return $url;
        }
        else if(is_array($url)) {
            return self::reverse($url);
        }

        if(!self::isRoot($url)) {
            if(!self::isHash($url)) {
                $url = $base . $url;
            }
            if($base == '/') {
                $url = self::here() . $url;
            }
        }

        $url = self::normalize(self::base() . $url);

        return $full ? self::domain() . $url : $url;
    }

    public static function isExternal($path) {
        return preg_match('/^[\w]+:/', $path);
    }

    public static function isRoot($url) {
        return substr($url, 0, 1) == '/';
    }

    public static function isHash($url) {
        return substr($url, 0, 1) == '#';
    }

    public static function reverse($path) {
        $here = self::parse();
        $params = $here['named'];
        $path = array_merge(array(
            'prefix' => $here['prefix'],
            'controller' => $here['controller'],
            'action' => $here['action'],
            'params' => $here['params']
        ), $params, $path);
        $nonParams = array('prefix', 'controller', 'action', 'params');
        $url = '';
        foreach($path as $key => $value) {
            if(!in_array($key, $nonParams)) {
                $url .= '/' . $key . ':' . $value;
            }
            else if(!is_null($value)) {
                if($key == 'action' && $filtered = self::filterAction($value)) {
                    $value = $filtered['action'];
                }
                else if($key == 'params') {
                    $value = join('/', $value);
                }
                $url .= '/' . $value;
            }
        }

        return $url;
    }

    public static function prefix($prefix) {
        self::instance()->prefixes []= $prefix;
    }

    public static function unsetPrefix($prefix) {
        unset(self::instance()->prefixes[$prefix]);
    }

    public static function prefixes() {
        return self::instance()->prefixes;
    }

    public static function connect($url, $route) {
        $url = self::normalize($url);
        self::instance()->routes[$url] = rtrim($route, '/');
    }

    public static function disconnect($url) {
        $url = rtrim($url, '/');
        unset(self::instance()->routes[$url]);
    }

    public static function match($check, $url = null) {
        if(is_null($url)) {
            $url = self::here();
        }
        $check = '%^' . str_replace(array(':any', ':fragment', ':num'), array('(.+)', '([^\/]+)', '([0-9]+)'), $check) . '/?$%';
        return preg_match($check, $url);
    }

    public static function getRoute($url) {
        $self = self::instance();
        foreach($self->routes as $map => $route) {
            if(self::match($map, $url)) {
                $map = '%^' . str_replace(array(':any', ':fragment', ':num'), array('(.+)', '([^\/]+)', '([0-9]+)'), $map) . '/?$%';
                $url = preg_replace($map, $route, $url);
                break;
            }
        }

        return self::normalize($url);
    }

    /*
        Method: parse
    */
    public static function parse($url = null) {
        $here = self::normalize(is_null($url) ? self::here() : $url);
        $url = self::getRoute($here);
        $prefixes = join('|', self::prefixes());

        $path = array();
        $parts = array('here', 'prefix', 'controller', 'action', 'extension', 'params', 'queryString');
        preg_match('/^\/(?:(' . $prefixes . ')(?:\/|(?!\w)))?(?:([a-z_-]*)\/?)?(?:([a-z_-]*)\/?)?(?:\.([\w]+))?(?:\/?([^?]+))?(?:\?(.*))?/i', $url, $reg);

        foreach($parts as $k => $key) {
            $path[$key] = isset($reg[$k]) ? $reg[$k] : null;
        }

        $path['named'] = $path['params'] = array();
        if(isset($reg[5])) {
            foreach(explode('/', $reg[5]) as $param) {
                if(preg_match('/([^:]*):([^:]*)/', $param, $reg)) {
                    $path['named'][$reg[1]] = urldecode($reg[2]);
                }
                else if($param != '') {
                    $path['params'] []= urldecode($param);
                }
            }
        }

        $path['here'] = $here;
        if(empty($path['controller'])) $path['controller'] = self::root();
        if(empty($path['action'])) $path['action'] = 'index';
        if($filtered = self::filterAction($path['action'])) {
            $path['prefix'] = $filtered['prefix'];
            $path['action'] = $filtered['action'];
        }
        if(!empty($path['prefix'])) {
            $path['action'] = $path['prefix'] . '_' . $path['action'];
        }
        if(empty($path['id'])) $path['id'] = null;
        if(empty($path['extension'])) $path['extension'] = 'htm';
        if(!empty($path['queryString'])) {
            parse_str($path['queryString'], $queryString);
            $path['named'] = array_merge($path['named'], $queryString);
        }

        return $path;
    }

    public static function filterAction($action) {
        if(strpos($action, '_') !== false) {
            foreach(self::prefixes() as $prefix) {
                if(strpos($action, $prefix) === 0) {
                    return array(
                        'action' => substr($action, strlen($prefix) + 1),
                        'prefix' => $prefix
                    );
                }
            }
        }
        return false;
    }
}