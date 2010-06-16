<?php

class Session {
    public static function start() {
        return session_start();
    }
    public static function started() {
        return isset($_SESSION);
    }
    public static function read($name) {
        if(!self::started()) self::start();
        return $_SESSION[$name];
    }
    public static function write($name, $value) {
        if(!self::started()) self::start();
        $_SESSION[$name] = $value;
    }
    public static function delete($name) {
        if(!self::started()) self::start();
        unset($_SESSION[$name]);
        return true;
    }
    public static function writeFlash($key, $value) {
        self::write('Flash.' . $key, $value);
    }
    public static function flash($key, $value = null) {
        if(!is_null($value)):
            return self::writeFlash($key, $value);
        endif;
        
        $value = self::read('Flash.' . $key);
        self::delete('Flash.' . $key);
        return $value;
    }
    public static function id() {
        if(!self::started()) self::start();
        return session_id();
    }
}