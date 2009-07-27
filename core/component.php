<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Component
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Component extends Object {
    public $components = array();
    public function init(&$controller) {
        if(is_array($controller->components)):
            $this->components = $controller->components;
        endif;
        foreach($this->components as $component):
            $component = "{$component}Component";
            $controller->{$component} = ClassRegistry::init($component, "Component");
        endforeach;
        return true;
    }
    public function initialize(&$controller) {
        foreach($this->components as $component):
            $component = "{$component}Component";
            $instance = $controller->{$component};
            if(method_exists($instance, "initialize")):
                $instance->initialize($controller);
            endif;
        endforeach;
    }
    public function startup(&$controller) {
        foreach($this->components as $component):
            $component = "{$component}Component";
            $instance = $controller->{$component};
            if(method_exists($instance, "startup")):
                $instance->startup($controller);
            endif;
        endforeach;
    }
    public function shutdown(&$controller) {
        foreach($this->components as $component):
            $component = "{$component}Component";
            $instance = $controller->{$component};
            if(method_exists($instance, "shutdown")):
                $instance->shutdown($controller);
            endif;
        endforeach;
    }
}

?>