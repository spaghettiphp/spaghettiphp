<?php
/**
 *  AuthComponent é o responsável pela autenticação e controle de acesso na aplicação.
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
        "id" => "id",
        "username" => "username",
        "password" => "password"
    );
    public $loggedIn;
    public $loginAction = "/users/login";
    public $logoutAction = "/users/logout";
    public $loginRedirect = "/";
    public $logoutRedirect = "/";
    public $user = array();

    public function initialize(&$controller) {
        $this->controller = $controller;
    }
    public function shutdown(&$controller) {
        if(Mapper::match($this->loginAction)):
            $this->login();
        elseif(Mapper::match($this->logoutAction)):
            $this->logout();
        endif;
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
                $user = $this->identify(array(
                    $this->fields["id"] => $user,
                    $this->fields["password"] => $password
                ));
                $this->loggedIn = !empty($user);
            else:
                $this->loggedIn = false;
            endif;
        endif;
        return $this->loggedIn;
    }
    public function identify($conditions) {
        $userModel = ClassRegistry::load($this->userModel);
        if(!$userModel):
            $this->error("missingModel", array("model" => $this->userModel));
            return false;
        endif;
        $params = array(
            "conditions" => array_merge(
                $this->userScope,
                $conditions
            )
        );
        return $this->user = $userModel->first($params);
    }
    public function user($field = null) {
        if(empty($this->user)):
            return null;
        endif;
        if(is_null($field)):
            return $this->user;
        else:
            return $this->user[$field];
        endif;
    }
    public function check() {
        if(!$this->authorized()):
            Cookie::write("action", Mapper::here());
            $this->controller->redirect($this->loginAction);
            return false;
        endif;
        return true;
    }
    public function login() {
        if(!$this->loggedIn()):
            if(!empty($this->controller->data)):
                $password = md5($this->controller->data[$this->fields["password"]]);
                $user = $this->identify(array(
                    $this->fields["username"] => $this->controller->data[$this->fields["username"]],
                    $this->fields["password"] => $password
                ));
                if(!empty($user)):
                    Cookie::write("user_id", $user[$this->fields["id"]]);
                    Cookie::write("password", $password);
                    $redirect = Cookie::read("action");
                    if(is_null($redirect)):
                        $redirect = $this->loginRedirect;
                    else:
                        Cookie::delete("action");
                    endif;
                    $this->controller->redirect($redirect);
                else:
                    $this->controller->set("authError", "wrongData");
                endif;
            endif;
        else:
            $this->controller->redirect($this->loginRedirect);
        endif;
    }
    public function logout() {
        Cookie::delete("user_id");
        Cookie::delete("password");
        $this->controller->redirect($this->logoutRedirect);
        return true;
    }
}

?>