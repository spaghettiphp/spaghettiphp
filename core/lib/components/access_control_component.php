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
      *  Short description.
      */
    public $userPermissions = array();
    /**
      *  Short description.
      */
    public $checkUserPermissions = false;
    
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
        if(!isset($this->permissions[$role])):
            $this->permissions[$role] = $permissions;
        else:
            $this->permissions[$role] = array_merge($this->permissions[$role], $permissions);
        endif;
    }
    /**
      *  Short description.
      *
      *  @param string $user
      *  @param array $permissions
      *  @return void
      */
    public function allowUser($user, $permissions) {
        $this->checkUserPermissions = true;
        if(!isset($this->permissions[$user])):
            $this->permissions[$user] = $permissions;
        else:
            $this->permissions[$user] = array_merge($this->permissions[$user], $permissions);
        endif;
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
                $here = Mapper::here();
                $roles = $this->getRoles();
                foreach($roles as $role):
                    foreach($this->permissions[$role] as $permission):
                        if(Mapper::match($permission, $here)):
                            return true;
                        endif;
                    endforeach;
                endforeach;
                if($this->checkUserPermissions):
                    $user = $this->auth->user($this->auth->fields["username"]);
                    foreach($this->userPermissions[$user] as $permission):
                        if(Mapper::match($permission, $here)):
                            return true;
                        endif;
                    endforeach;
                endif;
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
    /**
      *  Retorna os grupos ao qual o usuário atual pertence.
      *
      *  @return array Grupos ao qual o usuário pertence
      */
    public function getRoles() {
        $user = $this->auth->user();
        $userRoleModel = Inflector::underscore($this->userRoleModel);
        $roleModel = Inflector::underscore($this->roleModel);
        $roles = array();
        foreach($user[$userRoleModel] as $role):
            $roleName = $role[$roleModel]["name"];
            if(isset($this->permissions[$roleName])):
                $roles []= $roleName;
            endif;
        endforeach;
        return $roles;
    }
}

?>