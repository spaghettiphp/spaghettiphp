<?php

class AccessControlComponent extends Component {
    public $controller;
    public $auth;
    public $autoCheck = true;
    public $roleModel = "Roles";
    public $userRoleModel = "UsersRoles";
    public $permissions = array();
    public $userPermissions = array();
    public $checkGroupPermissions = true;
    public $checkUserPermissions = false;
    
    public function initialize($controller) {
        if(!isset($controller->AuthComponent)):
            trigger_error("Controller::AuthComponent not found", E_USER_ERROR);
        endif;
        $this->controller = $controller;
        $this->auth = $controller->AuthComponent;
        $this->auth->recursion = 1;
        $this->auth->deny();
    }
    public function startup($controller) {
        if($this->autoCheck):
            $this->check();
        endif;
    }
    public function allow($role, $permissions) {
        if(isset($this->permissions[$role])):
            $permissions = array_merge($this->permissions[$role], $permissions);
        endif;
        $this->permissions[$role] = $permissions;
    }
    public function allowUser($user, $permissions) {
        $this->checkUserPermissions = true;
        if(isset($this->permissions[$user])):
            $permissions = array_merge($this->userPermissions[$user], $permissions);
        endif;
        $this->userPermissions[$user] = $permissions;
    }
    public function authorized() {
        if($this->auth->loggedIn):
            if($this->auth->isPublic()):
                return true;
            else:
                return (
                    ($this->checkGroupPermissions && $this->authorizedGroup()) ||
                    ($this->checkUserPermissions && $this->authorizedUser())
                );
            endif;
        else:
            return $this->auth->authorized();
        endif;
    }
    public function authorizedGroup() {
        $here = Mapper::here();
        $roles = $this->getRoles();
        foreach($roles as $role):
            foreach($this->permissions[$role] as $permission):
                if(Mapper::match($permission, $here)):
                    return true;
                endif;
            endforeach;
        endforeach;
        return false;
    }
    public function authorizedUser() {
        $here = Mapper::here();
        $user = $this->auth->user($this->auth->fields["username"]);
        foreach($this->userPermissions[$user] as $permission):
            if(Mapper::match($permission, $here)):
                return true;
            endif;
        endforeach;
        return false;
    }
    public function check() {
        if(!$this->authorized()):
            $this->auth->setAction(Mapper::here());
            $this->auth->error($this->auth->authError);
            $this->controller->redirect($this->auth->loginAction);
            return false;
        endif;
        return true;
    }
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