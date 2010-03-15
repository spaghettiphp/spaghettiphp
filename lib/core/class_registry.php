<?php

class ClassRegistry {
    public $objects = array();

    public static function &getInstance() {
        static $instance = array();
        if (!$instance):
            $instance[0] = new ClassRegistry();
        endif;
        return $instance[0];
    }
    public static function &load($class, $type = 'Model') {
        $self =& ClassRegistry::getInstance();
        if($object =& $self->duplicate($class, $class)):
            return $object;
        elseif(!class_exists($class)):
            if(Loader::exists($type, Inflector::underscore($class))):
                require_once Loader::path($type, $class);
            endif;
        endif;
        if(class_exists($class)):
            ${$class} = new $class;
        endif;
        return ${$class};
    }
    public static function addObject($key, &$object) {
        $self =& ClassRegistry::getInstance();
        if(!array_key_exists($key, $self->objects)):
            $self->objects[$key] =& $object;
            return true;
        endif;
        return false;
    }
    public static function &getObject($key) {
        $self =& ClassRegistry::getInstance();
        $return = false;
        if(array_key_exists($key, $self->objects)):
            $return =& $self->objects[$key];
        endif;
        return $return;
    }
    public static function &duplicate($key, $class) {
        $self =& ClassRegistry::getInstance();
        $duplicate = false;
        if(array_key_exists($key, $self->objects)):
            $object =& self::getObject($key);
            if($object instanceof $class):
                $duplicate =& $object;
            endif;
            unset($object);
        endif;
        return $duplicate;
    }
}