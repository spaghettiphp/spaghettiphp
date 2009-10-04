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
     *  Instância do controller.
     */
    public $controller;
    /**
     *  Lista de permissões.
     */
    public $permissions = array();
    /**
     *  Autorização para URLs não especificadas explicitamente.
     */
    public $authorized = true;

    public function initialize(&$controller) {
        $this->controller = $controller;
    }
    /**
     *  Verifica se o usuário esta autorizado ou não para acessar a URL atual.
     *
     *  @return boolean Verdadeiro caso o usuário esteja autorizado a acessar a URL
     */
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
    /**
     *  Libera URLs a serem visualizadas sem autenticação.
     *
     *  @param string $url URL a ser liberada
     *  @return void
     */
    public function allow($url = null) {
        if(is_null($url)):
            $this->authorized = true;
        else:
            $this->permissions[$url] = true;
        endif;
    }
    /**
     *  Bloqueia os URLS para serem visualizadas apenas com autenticação.
     *
     *  @param string $url URL a ser bloqueada
     *  @return void
     */
    public function deny($url = null) {
        if(is_null($url)):
            $this->authorized = false;
        else:
            $this->permissions[$url] = false;
        endif;
    }
    public function login() {
        
    }
    public function logout() {
        
    }
}

?>