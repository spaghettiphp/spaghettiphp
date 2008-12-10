<?php

class AuthComponent extends Object {
    public $permissions = array();
    public $loggedIn = false;
    public $controller = null;
    public $data = array();
    public $params = array();
    public $userModel = null;
    public function initialize(&$controller) {
        $this->controller = $controller;
        $this->params = $controller->params;
        $this->data = $controller->data;
        $this->permissions = array(
            "prefix" => array(),
            "controller" => array("users" => true),
            "action" => array()
        );
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
            $this->controller->redirect("/users/login");
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
        if(isset($data["password"])):
            $data["password"] = md5($data["password"]);
        endif;
        return $data;
    }
    public function identify($data = array()) {
        $this->userModel = ClassRegistry::init("Users");
        $user = $this->userModel->find($data);
        return $user;
    }
    public function login() {
        if(!$this->loggedIn && !empty($this->data)):
            $user = $this->identify($this->hashPasswords($this->data));
            if(empty($user)):
                $this->controller->set("authError", "wrongData");
                return false;
            else:
                setcookie("user_id", $user["id"], null, "/");
                setcookie("user_password", $user["password"], null, "/");
                $this->loggedIn = true;
                return true;
            endif;
        endif;
    }
    public function logout() {
        setcookie("user_id", "", time() - 3600, "/");
        setcookie("user_password", "", time() - 3600, "/");
        $this->loggedIn = false;
        return true;
    }
    public function user($field = null) {
        $user_id = $_COOKIE["user_id"];
        $user = $this->identify(array("id" => $user_id));
        return $field === null ? $user : $user[$field];
    }
}

?>