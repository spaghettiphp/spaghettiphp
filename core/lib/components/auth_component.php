<?php
/**
 *  Short description.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class AuthComponent extends Component {
    /**
     *  Instância do controller usando o componente.
     */
    public $controller;
    /**
     *  Lista de permissões.
     */
    public $permissions = array();

    public function initialize(&$controller) {
        $this->controller = $controller;
    }
    public function allow($action) {
        
    }
    public function deny($url) {
        
    }
    public function login() {
        
    }
    public function logout() {
        
    }
}

?>