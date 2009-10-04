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
    
    public $userModel = "Users";
    public $userScope = array();
    public $fields = array(
        "username" => "username",
        "password" => "password"
    );
    public $loggedIn;

    public function initialize(&$controller) {
        $this->controller = $controller;
    }
    /**
     *  Verifica se o usuário esta autorizado ou não para acessar a URL atual.
     *
     *  @return boolean Verdadeiro caso o usuário esteja autorizado a acessar a URL
     */
    public function authorized() {
        if($this->loggedIn()):
            return true;
        else:
            $here = Mapper::here();
            $authorized = $this->authorized;
            foreach($this->permissions as $url => $permission):
                if(Mapper::match($url, $here)):
                    $authorized = $permission;
                endif;
            endforeach;
            return $authorized;
        endif;
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
    /**
     *  Short description.
     *
     *  @return boolean
     */
    public function loggedIn() {
        if(is_null($this->loggedIn)):
            $user = Cookie::read("user_id");
            $password = Cookie::read("password");
            if(!is_null($user) && !is_null($password)):
                $user = $this->identify($user, $password);
                $this->loggedIn = !empty($user);
            else:
                $this->loggedIn = false;
            endif;
        endif;
        return $this->loggedIn;
    }
    public function identify($id, $password) {
        $userModel = ClassRegistry::load($this->userModel);
        if(!$userModel):
            $this->error("missingModel", array("model" => $this->userModel));
            return false;
        endif;
        $params = array(
            "conditions" => array_merge(
                $this->userScope,
                array(
                    $userModel->primaryKey => $id,
                    $this->fields["password"] => $password
                )
            )
        );
        return $userModel->first($params);
    }
    public function login() {
        
    }
    public function logout() {
        
    }
}

?>