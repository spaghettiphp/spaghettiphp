<?php
/**
 *  Short description.
 *
 *  @author    José Cláudio Medeiros de Lima <contato@claudiomedeiros.net>
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2009, José Cláudio Medeiros de Lima <contato@claudiomedeiros.net>
 *
 */

class AccessControlComponent extends Component {
    /**
      *  Instância do controller.
      */
    public $controller;
    /**
      *  Instância do AuthComponent.
      */
    public $auth;
    /**
      *  Define se AuthComponent::check() será chamado automaticamente.
      */
    public $autoCheck = true;
    /**
      *  Nome do modelo a ser utilizado para grupos.
      */
    public $roleModel = "Roles";
    /**
      *  Nome do modelo a ser utilizado para relacionar grupos e usuários.
      */
    public $userRoleModel = "UsersRoles";
    
    /**
      *  Inicializa o componente.
      *
      *  @param object $controller Objeto Controller
      *  @return void
      */
    public function initialize(&$controller) {
        if(!isset($controller->AuthComponent)):
            trigger_error("Controller::AuthComponent not found", E_USER_ERROR);
        endif;
        $this->controller = $controller;
        $this->auth = $controller->AuthComponent;
        $this->auth->deny();
    }
    /**
      *  Faz as operações necessárias após a inicialização do componente.
      *
      *  @param object $controller Objeto Controller
      *  @return void
      */
    public function startup(&$controller) {
        if($this->autoCheck):
            $this->check();
        endif;
    }
    /**
      *  Short description.
      */
    public function allow($group, $permissions) {
        
    }
    /**
      *  Short description.
      */
    public function allowUser($user, $permissions) {
        
    }
    /**
     *  Verifica se o usuário esta autorizado ou não para acessar a URL atual.
     *
     *  @return boolean Verdadeiro caso o usuário esteja autorizado
     */
    public function authorized() {
        if($this->auth->loggedIn):
            $here = Mapper::here();
            $authorized = $this->auth->authorized;
            foreach($this->auth->permissions as $url => $permission):
                if(Mapper::match($url, $here)):
                    $authorized = $permission;
                endif;
            endforeach;
            if($authorized) return true;
            elseif(Mapper::here() != "/home")
                return true;
            else
                return false;
        else:
            return $this->auth->authorized();
        endif;
    }
    /**
      *  Verifica se o usuário está autorizado a acessar a URL atual, tomando as
      *  ações necessárias no caso contrário.
      *
      *  @return boolean Verdadeiro caso o usuário esteja autorizado
      */
    public function check() {
        if(!$this->authorized()):
            Cookie::write("action", Mapper::here());
            $this->auth->error("notAuthorized");
            $this->controller->redirect($this->auth->loginAction);
            return false;
        endif;
        return true;
    }
}

?>