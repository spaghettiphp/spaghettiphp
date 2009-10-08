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
    public $permissions = array();
    
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
      *  Permite acesso a um grupo de usuários.
      *
      *  @param string $group Grupo a receber a permissão
      *  @param array $permissions Permissões a serem dadas ao grupo
      *  @return void
      */
    public function allow($group, $permissions) {
        if(!isset($permissions[$group])):
            $permissions[$group] = $permissions;
        else:
            $permissions[$group] = array_merge($permissions[$group], $permissions);
        endif;
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
            if($this->auth->isPublic()):
                return true;
            elseif(Mapper::here() != "/home"):
                return true;
            else:
                return false;
            endif;
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