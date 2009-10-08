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
      *  Short description.
      */
    public $controller;
    /**
      *  Short description.
      */
    public $auth;
    /**
      *  Short description.
      */
    public $autoCheck = true;
    /**
      *  Short description.
      */
    public $roleModel = "Roles";
    /**
      *  Short description.
      */
    public $userRoleModel = "UsersRoles";
    
    /**
      *  Short description.
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
      *  Short description.
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
      *  Short description.
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
            elseif(Mapper::here() == "/home")
                return true;
            else
                return false;
        else:
            return $this->auth->authorized();
        endif;
    }
    /**
      *  Short description.
      */
    public function check() {
        if(!$this->authorized()):
            Cookie::write("action", Mapper::here());
            $this->auth->error("notAuthorized");
            $this->controller->redirect($this->loginAction);
            return false;
        endif;
        return true;
    }
}

?>