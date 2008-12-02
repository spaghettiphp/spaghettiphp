<?php
/**
 *  A classe ClassRegistry faz o registro e gerenciamento de instâncias
 *  das classes usadas pelo core e pela aplicação Spaghetti, com o fim de
 *  evitar que várias instâncias de uma mesma classe sejam criadas
 *  desnecessariamente.
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
    /**
     * O método ClassRegistry::get_instance() retorna a instância atual do objeto
     * ou cria uma nova instância caso ainda não exista.
     *
     * @return resource
     */
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
    /**
     * O método ClassRegistry::add_object() adiciona uma instância de uma
     * classe do registro.
     *
     * @param string $key Nome da chave
     * @param object &$object Referência ao objeto a ser registrado
     * @return boolean
     */
    public function add_object($key, &$object) {
        $self =& ClassRegistry::get_instance();
        if(array_key_exists($key, $self->objects) === false):
	    $self->objects[$key] =& $object;
	    return true;
        endif;
        return false;
    }
    /**
     * O método ClassRegistry::remove_object() remove uma instância de uma
     * classe do registro.
     * 
     * @param string $key Nome da chave
     * @return void
     */
    public function remove_object($key) {
        $self =& ClassRegistry::get_instance();
        if(array_key_exists($key, $self->objects) === true):
	    unset($self->objects[$key]);
        endif;
    }
    /**
     * O método ClassRegistry::is_key_set() verifica se uma se uma chave já
     * está registrada.
     *
     * @param string $key Nome da chave
     * @return boolean Se a chave existe ou não.
     */
    public function is_key_set($key) {
        $self =& ClassRegistry::get_instance();
        if(array_key_exists($key, $self->objects)):
	    return true;
        endif;
        return false;
    }
    /**
     * O método ClassRegistry::keys() retorna um array das chaves das classes
     * registradas.
     *
     * @return array
     */
    public function keys() {
        $self =& ClassRegistry::get_instance();
        return array_keys($self->objects);
    }
    /**
     * O método ClassRegistry::get_object() retorna a instância do objeto
     * solicitado.
     *
     * @param string $key Nome do objeto
     * @return resource Objeto
     */
    public function &get_object($key) {
        $self =& ClassRegistry::get_instance();
        $key = $key;
        $return = false;
        if(isset($self->objects[$key])):
	    $return =& $self->objects[$key];
        endif;
        return $return;
    }
    /**
     * O método ClassRegistry::duplicate() cria uma cópia de uma instância
     * já registrada.
     *
     * @param string $alias
     * @param $class
     * @return
     */
    public function &duplicate($alias, $class) {
        $self =& ClassRegistry::get_instance();
        $duplicate = false;
        if ($self->is_key_set($alias)):
	    $model =& $self->get_object($alias);
	    if(is_a($model, $class) || $model->alias === $class):
		$duplicate =& $model;
	    endif;
	    unset($model);
        endif;
        return $duplicate;
    }
    /**
     * O método ClassRegistry::flush() limpa todos os objetos instanciados
     * até então pela aplicação e pelo core.
     *
     * @return void
     */
    public function flush() {
	$self =& ClassRegistry::get_instance();
	$self->objects = array();
    }
}

?>