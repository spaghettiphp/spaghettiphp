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
     *  Lista de permissões de grupo.
     */
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
        $this->auth->recursion = 2;
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
      *  @param string $role Grupo a receber a permissão
      *  @param array $permissions Permissões a serem dadas ao grupo
      *  @return void
      */
    public function allow($role, $permissions) {
        if(!isset($permissions[$role])):
            $permissions[$role] = $permissions;
        else:
            $permissions[$role] = array_merge($permissions[$role], $permissions);
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
            else:
                $roles = $this->getRoles();
                if($this->hasRole($roles)):
                    // check for permissions
                        // check for user
                else:
                    return false;
                endif;
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
    /**
      *  Short description.
      *
      *  @return array
      */
    public function getRoles() {
        $user = $this->auth->user();
        $userRoleModel = Inflector::underscore($this->userRoleModel);
        $roleModel = Inflector::underscore($this->roleModel);
        $roles = array();
        foreach($user[$userRoleModel] as $role):
            $roles []= $role[$roleModel]["name"];
        endforeach;
        return $roles;
    }
    /**
      *  Short description.
      *
      *  @param array $roles
      *  @return boolean
      */
    public function hasRole($roles) {
        $allowedRoles = array_keys($this->permissions);
        $diff = array_diff($allowedRoles, $roles);
        return count($allowedRoles) != count($diff);
    }
}

?>