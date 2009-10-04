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
    public $authorized = true;

    public function initialize(&$controller) {
        $this->controller = $controller;
    }
    public function authorized() {
        $here = Mapper::here();
        $authorized = $this->authorized;
        foreach($this->permissions as $url => $permission):
            if(Mapper::match($url, $here)):
                $authorized = $permission;
            endif;
        endforeach;
        return $authorized;
    }
    public function allow($url = null) {
        if(is_null($url)):
            $this->authorized = true;
        else:
            
        endif;
    }
    public function deny($url = null) {
        if(is_null($url)):
            $this->authorized = false;
        else:
            
        endif;
    }
    public function login() {
        
    }
    public function logout() {
        
    }
}

?>