<?php

// @todo components should be replaced with plugins
class Component {
    public static function load($name, $instance = false) {
        if(!class_exists($name) && Filesystem::exists('lib/components/' . $name . '.php')):
            require_once 'lib/components/' . $name . '.php';
        endif;
        if(class_exists($name)):
            if($instance):
                return new $name();
            else:
                return true;
            endif;
        else:
            throw new MissingComponentException(array(
                'component' => $name
            ));
        endif;        
    }
    public function initialize($controller) { }
    public function startup($controller) { }
    public function shutdown($controller) { }
}