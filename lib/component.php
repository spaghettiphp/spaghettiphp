<?php
/**
 *  Component é uma classe abstrata, sendo herdada por todos os componentes dentro
 *  do Spaghetti, e definindo apenas os métodos básicos para seu funcionamento.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

abstract class Component extends Object {
    /**
     *  Callback executado antes de qualquer ação do controller.
     *
     *  @param object $controller
     *  @return true
     */
    public function initialize(&$controller) {
        return true;
    }
    /**
     *  Callback executado após Controller::beforeFilter.
     *
     *  @param object $controller
     *  @return true
     */
    public function startup(&$controller) {
        return true;
    }
    /**
     *  Callback executado após todas as ações do controller, mas antes de enviar
     *  a saída renderizada.
     *
     *  @param object $controller
     *  @return true
     */
    public function shutdown(&$controller) {
        return true;
    }
}

?>