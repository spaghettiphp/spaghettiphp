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

class ClassRegistry {
    public $objects = array();
	public function &get_instance() {
	    static $instance = array();
	    if (!$instance):
		$instance[0] =& new ClassRegistry();
	    endif;
	    return $instance[0];
	}
	public function &init($class, $type = "Model") {
	    $self =& ClassRegistry::get_instance();
	    if($model =& $self->duplicate($class, $class)):
		return $model;
	    elseif(class_exists($class) || Spaghetti::import($type, Inflector::underscore($class))):
		${$class} =& new $class;
	    else:
		$this->error("missing{$type}", array($type => $class));
	    endif;
	    return ${$class};
	}
	public function add_object($key, &$object) {
	    $self =& ClassRegistry::get_instance();
	    if(array_key_exists($key, $self->objects) === false):
		$self->objects[$key] =& $object;
		return true;
	    endif;
	    return false;
	}
	public function remove_object($key) {
	    $self =& ClassRegistry::get_instance();
	    if(array_key_exists($key, $self->objects) === true):
		unset($self->objects[$key]);
	    endif;
	}
	public function is_key_set($key) {
	    $self =& ClassRegistry::get_instance();
	    if(array_key_exists($key, $self->objects)):
		return true;
	    endif;
	    return false;
	}
	public function keys() {
	    $self =& ClassRegistry::get_instance();
	    return array_keys($self->objects);
	}
	public function &get_object($key) {
	    $self =& ClassRegistry::get_instance();
	    $key = $key;
	    $return = false;
	    if(isset($self->objects[$key])):
		$return =& $self->objects[$key];
	    endif;
	    return $return;
	}
	public function &duplicate($alias, $class) {
	    $self =& ClassRegistry::get_instance();
	    $duplicate = false;
	    if ($self->is_key_set($alias)) {
		$model =& $self->get_object($alias);
		if(is_a($model, $class) || $model->alias === $class) {
		    $duplicate =& $model;
		}
		unset($model);
	    }
	    return $duplicate;
	}
	public function flush() {
	    $self =& ClassRegistry::get_instance();
	    $self->objects = array();
	}
}

?>