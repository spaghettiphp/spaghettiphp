<?php

class Auth {
    const SESSION_KEY = 'Auth.user';

    public static function login($user) {
        Session::regenerate();
        Session::write(self::SESSION_KEY, serialize($user));
    }

    public static function logout() {
        Session::destroy();
    }

    public static function identify($data) {
        return Model::load('Users')->first(array(
            'conditions' => array(
                'username' => $data['username'],
                'password' => Security::hash($data['password'])
            ),
            'orm' => true
        ));
    }

    public static function loggedIn() {
        return !is_null(Session::read(self::SESSION_KEY));
    }

    public static function user() {
        Model::load('Users');
        return unserialize(Session::read(self::SESSION_KEY));
    }
}