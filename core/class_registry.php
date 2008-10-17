<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.ClassRegistry
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class ClassRegistry extends Object {
    public $objects = array();
    public function &get_instance() {
        static $instance = array();
        
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] =& new ClassRegistry();
        endif;
        
        return $instance[0];
    }
    public function &set_object($type = "Model", $class = null) {
        $self =& ClassRegistry::get_instance();
        
        if(!class_exists($class)):
            if(!Spaghetti::import($type, Inflector::underscore($class))):
                $this->error("missing{$type}", array(Inflector::underscore($type) => $class));
            endif;
        endif;
        
        $self->objects[$type][$class] =& new $class;
        return $self->objects[$type][$class];
    }
    public function &get_object($type = "Model", $class = null) {
        $self =& ClassRegistry::get_instance();
        
        if(!isset($self->objects[$type][$class])):
            return $self->set_object($type, $class);
        endif;
        
        return $self->objects[$type][$class];
    }
    public function flush() {
        $self =& ClassRegistry::get_instance();
        $self->objects = array();
    }
}

?>