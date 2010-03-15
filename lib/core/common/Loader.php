<?php

class Loader {
    public static function import($type, $file) {
    }
    public static function instance($type, $class) {
        if(!class_exists($class)):
            App::import($type, Inflector::underscore($class));
        endif;
        return new $class;
    }
}