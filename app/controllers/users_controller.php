<?php

class UsersController extends AppController {
    public function login() {
    }
    public function logout() {
        $this->AuthComponent->logout();
    }
}

?>