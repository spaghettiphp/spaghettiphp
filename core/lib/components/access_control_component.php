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
    public $autoCheck = true;
    public $roleModel = "Roles";
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
    public function authorized() {
        if($this->auth->loggedIn):
            return true;
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