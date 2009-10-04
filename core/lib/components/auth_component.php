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
    public function authorized() {
        $here = Mapper::here();
        $authorized = true;
        foreach($this->permissions as $url => $permission):
            if(Mapper::match($url, $here)):
                $authorized = $permission;
            endif;
        endforeach;
        return $authorized;
    }
    public function allow($url = null) {
        
    }
    public function deny($url = null) {
        
    }
    public function login() {
        
    }
    public function logout() {
        
    }
}

?>