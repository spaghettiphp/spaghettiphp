<?php

class AuthComponent extends Component {
    public $authorized = true;
    public $autoCheck = true;
    public $controller;
    public $fields = array(
        "id" => "id",
        "username" => "username",
        "password" => "password"
    );
    public $hash = "sha1";
    public $loggedIn;
    public $loginAction = "/users/login";
    public $loginRedirect = "/";
    public $logoutAction = "/users/logout";
    public $logoutRedirect = "/";
    public $permissions = array();
    public $user = array();
    public $userModel = "Users";
    public $userScope = array();
    public $useSalt = true;
    public $expires;
    public $path = "/";
    public $domain = "";
    public $secure = false;
    public $recursion;
    public $loginError = "loginFailed";
    public $authError = "notAuthorized";
    public $authenticate = false;

    public function initialize($controller) {
        $this->controller = $controller;
    }
    public function startup($controller) {
        $this->allow($this->loginAction);
        if($this->autoCheck):
            $this->check();
        endif;
        if(Mapper::match($this->loginAction)):
            $this->login();
        endif;
    }
    public function shutdown($controller) {
        if(Mapper::match($this->loginAction)):
            $this->loginRedirect();
        endif;
    }
    public function check() {
        if(!$this->authorized()):
            $this->setAction(Mapper::here());
            $this->error($this->authError);
            $this->controller->redirect($this->loginAction);
            return false;
        endif;
        return true;
    }
    public function authorized() {
        return $this->loggedIn() || $this->isPublic();
    }
    public function isPublic() {
        $here = Mapper::here();
        $authorized = $this->authorized;
        foreach($this->permissions as $url => $permission):
            if(Mapper::match($url, $here)):
                $authorized = $permission;
            endif;
        endforeach;
        return $authorized;
    }
    public function allow($url = null) {
        if(is_null($url)):
            $this->authorized = true;
        else:
            $this->permissions[$url] = true;
        endif;
    }
    public function deny($url = null) {
        if(is_null($url)):
            $this->authorized = false;
        else:
            $this->permissions[$url] = false;
        endif;
    }
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
        $userModel = Loader::instance("Model", $this->userModel);
        if(!$userModel):
            $this->error("missingModel", array("model" => $this->userModel));
            return false;
        endif;
        $params = array(
            "conditions" => array_merge(
                $this->userScope,
                $conditions
            ),
            "recursion" => is_null($this->recursion) ? $userModel->recursion : $this->recursion
        );
        return $this->user = $userModel->first($params);
    }
    public function hash($password) {
        return Security::hash($password, $this->hash, $this->useSalt);
    }
    public function login() {
        if(!empty($this->controller->data)):
            $password = $this->hash($this->controller->data[$this->fields["password"]]);
            $user = $this->identify(array(
                $this->fields["username"] => $this->controller->data[$this->fields["username"]],
                $this->fields["password"] => $password
            ));
            if(!empty($user)):
                $this->authenticate = true;
            else:
                $this->error($this->loginError);
            endif;
        endif;
    }
    public function loginRedirect() {
        if($this->authenticate):
            $this->authenticate($this->user["id"], $this->user["password"]);
            if($redirect = $this->getAction()):
                $this->loginRedirect = $redirect;
            endif;
            $this->controller->redirect($this->loginRedirect);
        endif;
    }
    public function authenticate($id, $password) {
        Cookie::set("domain", $this->domain);
        Cookie::set("path", $this->path);
        Cookie::set("secure", $this->secure);
        Cookie::write("user_id", $id, $this->expires);
        Cookie::write("password", $password, $this->expires);
    }
    public function logout() {
        Cookie::set("domain", $this->domain);
        Cookie::set("path", $this->path);
        Cookie::set("secure", $this->secure);
        Cookie::delete("user_id");
        Cookie::delete("password");
        $this->controller->redirect($this->logoutRedirect);
    }
    public function user($field = null) {
        if($this->loggedIn()):
            if(is_null($field)):
                return $this->user;
            else:
                return $this->user[$field];
            endif;
        else:
            return null;
        endif;
    }
    public function setAction($action) {
        Session::write("Auth.action", $action);
    }
    public function getAction() {
        $action  = Session::read("Auth.action");
        Session::delete("Auth.action");
        return $action;
    }
    public function error($type, $details = array()) {
        Session::writeFlash("Auth.error", $type);
    }
}