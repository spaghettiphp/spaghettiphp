<?php

class Loader {
    public static function path($type, $class, $ext = 'php') {
        $paths = array(
            'Controller' => '/controllers/',
            'Model' => '/models/',
            'View' => '/views/',
            'Layout' => '/views/layouts/',
            'Component' => '/components/',
            'Helper' => '/helpers/'
        );
        
        return SPAGHETTI_ROOT . '/app/' . $paths[$type] . Inflector::underscore($class) . '.' . $ext;
    }
    public static function exists($type, $class) {
        return file_exists(self::path($type, $class));
    }
    public static function instance($type, $class) {
        if(!class_exists($class)):
            require_once Loader::path($type, $class);
        endif;
        
        return new $class;
    }
}