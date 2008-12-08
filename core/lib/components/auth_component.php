<?php

class AuthComponent extends Object {
    public $permissions = array();
    public function initialize(&$controller) {
        $this->controller = $controller;
        $this->params = $controller->params;
        $this->permissions = array(
            "prefix" => array(),
            "controller" => array("users" => true),
            "action" => array()
        );
    }
    public function authorized() {
        $authorized = true;
        if($_COOKIE["username"] && $_COOKIE["password"]):
            return true;
        endif;
        foreach($this->params as $param => $value):
            if($this->permissions[$param][$value] === false):
                $authorized = false;
            elseif($this->permissions[$param][$value] === true):
                $authorized = true;
            endif;
        endforeach;
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
    public function authenticate($data = array()) {
        $user = $data["user"];
        $password = md5($data["password"]);
        $model = ClassRegistry::init("Users");
        $results = $model->find(array("user" => $user, "password" => $password));
        if(!empty($results)):
            setcookie("username", $user, null, "/");
            setcookie("password", $password, null, "/");
        endif;
    }
    public function unauthenticate() {
        setcookie("username", $user, time() - 3600, "/");
        setcookie("password", $password, time() - 3600, "/");
    }
    public function login() {
        if(!empty($this->controller->data)):
            $this->authenticate($this->controller->data);
        endif;
    }
    public function logout() {
        
    }
}

?>