<?php

class ClassRegistry {
    public $objects = array();
    protected static $instance;

    public static function instance() {
        if(!isset(self::$instance)):
            $c = __CLASS__;
            self::$instance = new $c;
        endif;
        return self::$instance;
    }
    public static function load($class) {
        $self = self::instance();
        if($object = $self->duplicate($class, $class)):
            return $object;
        elseif(!class_exists($class)):
            if(Loader::exists('Model', Inflector::underscore($class))):
                require_once Loader::path('Model', $class);
            endif;
        endif;
        if(class_exists($class)):
            ${$class} = new $class;
        endif;
        return ${$class};
    }
    public static function addObject($key, $object) {
        $self = self::instance();
        if(!array_key_exists($key, $self->objects)):
            $self->objects[$key] = $object;
            return true;
        endif;
        return false;
    }
    public static function getObject($key) {
        $self = self::instance();
        $return = false;
        if(array_key_exists($key, $self->objects)):
            $return = $self->objects[$key];
        endif;
        return $return;
    }
    public static function duplicate($key, $class) {
        $self = self::instance();
        $duplicate = false;
        if(array_key_exists($key, $self->objects)):
            $object = self::getObject($key);
            if($object instanceof $class):
                $duplicate = $object;
            endif;
            unset($object);
        endif;
        return $duplicate;
    }
}