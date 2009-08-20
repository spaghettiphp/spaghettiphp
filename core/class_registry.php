<?php
/**
 *  ClassRegistry faz o registro e gerenciamento de instâncias das classes utilizadas
 *  pelo Spaghetti*, evitando a criação de várias instâncias de uma mesma classe.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class ClassRegistry {
    /**
     *  Short description.
     */
    public $objects = array();

    public static function &getInstance() {
        static $instance = array();
        if (!$instance):
            $instance[0] = new ClassRegistry();
        endif;
        return $instance[0];
    }
    /**
     *  Short description.
     *
     *  @param string $class
     *  @param string $type
     *  @return object
     */
    public static function &load($class, $type = "Model") {
        $self =& ClassRegistry::getInstance();
        if($object =& $self->duplicate($class, $class)):
            return $object;
        elseif(!class_exists($class)):
            if(App::path($type, Inflector::underscore($class))):
                App::import($type, Inflector::underscore($class));
            endif;
        endif;
        if(class_exists($class)):
            ${$class} = new $class;
            return ${$class};
        else:
            return false;
        endif;
    }
    /**
     *  Short description.
     * 
     *  @param string $key
     *  @param object &$object
     *  @return boolean
     */
    public static function addObject($key, &$object) {
        $self =& ClassRegistry::getInstance();
        if(array_key_exists($key, $self->objects) === false):
            $self->objects[$key] =& $object;
            return true;
        endif;
        return false;
    }
    /**
     *  Short description.
     *  
     *  @param string $key
     *  @return boolean true
     */
    public static function removeObject($key) {
        $self =& ClassRegistry::getInstance();
        if(array_key_exists($key, $self->objects) === true):
            unset($self->objects[$key]);
        endif;
        return true;
    }
    /**
     *  Short description.
     * 
     *  @param string $key
     *  @return boolean
     */
    public static function isKeySet($key) {
        $self =& ClassRegistry::getInstance();
        if(array_key_exists($key, $self->objects)):
            return true;
        endif;
        return false;
    }
    /**
     *  Short description.
     * 
     *  @param string $key
     *  @return mixed
     */
    public static function &getObject($key) {
        $self =& ClassRegistry::getInstance();
        $return = false;
        if(self::isKeySet($key)):
            $return =& $self->objects[$key];
        endif;
        return $return;
    }
    /**
     *  Short description.
     * 
     *  @param string $key
     *  @param object $class
     *  @return mixed
     */
    public static function &duplicate($key, $class) {
        $self =& ClassRegistry::getInstance();
        $duplicate = false;
        if (self::isKeySet($key)):
            $object =& self::getObject($key);
            if($object instanceof $class):
                $duplicate =& $object;
            endif;
            unset($object);
        endif;
        return $duplicate;
    }
    /**
     *  Short description.
     * 
     *  @return boolean true
     */
    public static function flush() {
        $self =& ClassRegistry::getInstance();
        $self->objects = array();
        return true;
    }
}

?>