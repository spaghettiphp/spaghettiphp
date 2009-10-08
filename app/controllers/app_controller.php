<?php

class AppController extends Controller {
    public $components = array("Auth", "AccessControl");
    public function beforeFilter() {
        $this->AuthComponent->allow("/");
        $this->AccessControlComponent->allowUser("juliogreff", array("/home/index"));
    }
}

?>