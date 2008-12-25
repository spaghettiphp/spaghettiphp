<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Lib.Component.Auth
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class AuthComponent extends Object {
    public $permissions = null;
    public $loggedIn = false;
    public $controller = null;
    public $data = array();
    public $params = array();
    public $userModel = "Users";
    public $userScope = array();
    public $loginAction = "/users/login";
    public $loginRedirect = "/";
    public $logoutRedirect = "/";
    public $fields = array(
        "username" => "username",
        "password" => "password"
    );
    public function initialize(&$controller) {
        $this->controller = $controller;
        $this->params = $controller->params;
        $this->data = $controller->data;
        if($this->permissions === null):
            $this->permissions = array(
                "prefix" => array(),
                "controller" => array("users" => true),
                "action" => array()
            );
        endif;
    }
    public function authorized() {
        $authorized = true;
        if($_COOKIE["user_id"] && $_COOKIE["user_password"]):
            $data = array("id" => $_COOKIE["user_id"], "password" => $_COOKIE["user_password"]);
            $identify = $this->identify($data);
            if(!empty($identify)):
                $this->loggedIn = true;
                $authorized = true;
            endif;
        else:
            foreach($this->params as $param => $value):
                if($this->permissions[$param][$value] === false):
                    $authorized = false;
                elseif($this->permissions[$param][$value] === true):
                    $authorized = true;
                endif;
            endforeach;
        endif;
        return $authorized;
    }
    public function check() {
        if(!$this->authorized()):
            $this->controller->redirect($this->loginAction);
            return false;
        endif;
        return true;
    }
    public function allow($permissions = array()) {
        if($permissions == "" || $permissions == "*"):
            $this->permissions["prefix"][""] = true;
        else:
            foreach($permissions as $resource => $permission):
                $this->permissions[$resource][$permission] = true;
            endforeach;
        endif;
        return true;
    }
    public function deny($permissions = array()) {
        if($permissions == "" || $permissions == "*"):
            $this->permissions["prefix"][""] = false;
        else:
            foreach($permissions as $resource => $permission):
                $this->permissions[$resource][$permission] = false;
            endforeach;
        endif;
        return true;
    }
    public function hashPasswords($data = array()) {
        if(isset($data[$this->fields["password"]])):
            $data[$this->fields["password"]] = md5($data[$this->fields["password"]]);
        endif;
        return $data;
    }
    public function identify($data = array()) {
        $userModel = ClassRegistry::init($this->userModel);
        $user = $userModel->find(array_merge($this->userScope, $data));
        return $user;
    }
    public function login() {
        if(!$this->loggedIn):
            if(!empty($this->data)):
                $user = $this->identify($this->hashPasswords($this->data));
                if(empty($user)):
                    $this->controller->set("authError", "wrongData");
                    return false;
                else:
                    setcookie("user_id", $user["id"], null, "/");
                    setcookie("user_password", $user[$this->fields["password"]], null, "/");
                    $this->loggedIn = true;
                    $this->controller->redirect($this->loginRedirect);
                    return true;
                endif;
            endif;
        else:
            $this->controller->redirect($this->loginRedirect);
        endif;
    }
    public function logout() {
        setcookie("user_id", "", time() - 3600, "/");
        setcookie("user_password", "", time() - 3600, "/");
        $this->loggedIn = false;
        $this->controller->redirect($this->logoutRedirect);
        return true;
    }
    public function user($field = null) {
        $user_id = $_COOKIE["user_id"];
        $user = $this->identify(array("id" => $user_id));
        return $field === null ? $user : $user[$field];
    }
}

?>