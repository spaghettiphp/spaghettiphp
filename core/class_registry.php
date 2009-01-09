<?php
/**
 *  ClassRegistry faz o registro e gerenciamento de instâncias das classes utilizadas
 *  pelo Spaghetti, evitando a criação de várias instâncias de uma mesma classe.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.ClassRegistry
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 */

class ClassRegistry {
    public $objects = array();
    public function &getInstance() {
        static $instance = array();
        if (!$instance):
            $instance[0] =& new ClassRegistry();
        endif;
        return $instance[0];
    }
    public function &load($class, $type = "Model") {
        $self =& ClassRegistry::getInstance();
        if($object =& $self->duplicate($class, $class)):
            return $object;
        elseif(class_exists($class) || App::import($type, Inflector::underscore($class))):
            ${$class} =& new $class;
        else:
            return false;
        endif;
        return ${$class};
    }
    public function &init($class, $type = "Model") {
        $self =& ClassRegistry::getInstance();
        if($model =& $self->duplicate($class, $class)):
            return $model;
        elseif(class_exists($class) || App::import($type, Inflector::underscore($class))):
            ${$class} =& new $class;
        else:
            $this->error("missing{$type}", array(strtolower($type) => $class));
        endif;
        return ${$class};
    }
    /**
     *  ClassRegistry::addObject() adiciona uma instância de uma classe no registro.
     * 
     *  @param string $key Nome da chave
     *  @param object &$object Referência ao objeto a ser registrado
     *  @return boolean Verdadeiro se a instância foi adicionada, falso se a chave
     *  já existir
     */
    public function addObject($key, &$object) {
        $self =& ClassRegistry::getInstance();
        if(array_key_exists($key, $self->objects) === false):
            $self->objects[$key] =& $object;
            return true;
        endif;
        return false;
    }
    /**
     *  ClassRegistry::removeObject() remove uma instância de uma classe do registro.
     *  
     *  @param string $key Nome da chave
     *  @return boolean true
     */
    public function removeObject($key) {
        $self =& ClassRegistry::getInstance();
        if(array_key_exists($key, $self->objects) === true):
            unset($self->objects[$key]);
        endif;
        return true;
    }
    /**
     *  ClassRegistry::isKeySet() verifica se uma se uma chave já está registrada.
     * 
     *  @param string $key Nome da chave
     *  @return boolean Verdadeiro se a chave está registrada
     */
    public function isKeySet($key) {
        $self =& ClassRegistry::getInstance();
        if(array_key_exists($key, $self->objects)):
            return true;
        endif;
        return false;
    }
    /**
     *  ClassRegistry::getObject() retorna a instância da respectiva chave solicitada.
     * 
     *  @param string $key Nome da chave
     *  @return mixed Objeto correspondente a chave, falso se a chave não existe
     */
    public function &getObject($key) {
        $self =& ClassRegistry::getInstance();
        $return = false;
        if(self::isKeySet($key)):
            $return =& $self->objects[$key];
        endif;
        return $return;
    }
    /**
     *  ClassRegistry::duplicate() retorna uma cópia de uma instância já registrada.
     * 
     *  @param string $key Chave da instância a ser buscada
     *  @param object $class Instância da classe a ser buscada
     *  @return mixed Instância da classe, falso se não estiver definida no registro
     */
    public function &duplicate($key, $class) {
        $self =& ClassRegistry::getInstance();
        $duplicate = false;
        if (self::isKeySet($key)):
            $object =& self::getObject($key);
            if(is_a($object, $class)):
                $duplicate =& $object;
            endif;
            unset($object);
        endif;
        return $duplicate;
    }
    /**
     *  ClassRegistry::flush() limpa todos os objetos instanciados do registro.
     * 
     *  @return boolean true
     */
    public function flush() {
        $self =& ClassRegistry::getInstance();
        $self->objects = array();
        return true;
    }
}

?>