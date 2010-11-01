<?php

class Cookie {
    public $expires;
    public $path = '/';
    public $domain = '';
    public $secure = false;
    public $key;
    public $name = 'SpaghettiCookie';
    public static $instance;
    
    public function __construct() {
        $this->key = Config::read('Security.salt');
    }
    public static function getInstance() {
        if(!isset(self::$instance)):
            $c = __CLASS__;
            self::$instance = new $c;
        endif;
        return self::$instance;
    }
    public static function set($key, $value) {
        $self = self::getInstance();
        if(isset($self->$key)):
            $self->$key = $value;
            return true;
        endif;
        return false;
    }
    public static function get($key) {
        $self = self::getInstance();
        if(isset($self->$key)):
            return $self->$key;
        endif;
        return null;
    }
    public static function delete($name) {
        $self = self::getInstance();
        $path = Mapper::normalize(Mapper::base() . $self->path);
        return setcookie($self->name . '[' . $name . ']', '', time() - 42000, $path, $self->domain, $self->secure);
    }
    public static function read($name) {
        $self = self::getInstance();
        if(array_key_exists($self->name, $_COOKIE)):
            return self::decrypt($_COOKIE[$self->name][$name]);
        endif;
        return null;
    }
    public static function write($name, $value, $expires = null) {
        $self = self::getInstance();
        $expires = $self->expire($expires);
        $path = Mapper::normalize(Mapper::base() . $self->path);
        return setcookie($self->name . '[' . $name . ']', self::encrypt($value), $expires, $path, $self->domain, $self->secure, true);
    }
    public static function encrypt($value) {
        $self = self::getInstance();
        $encripted = base64_encode(Security::cipher($value, $self->key));
        return 'U3BhZ2hldHRp.' . $encripted;
    }
    public static function decrypt($value) {
        $self = self::getInstance();
        $prefix = strpos($value, 'U3BhZ2hldHRp.');
        if($prefix !== false):
            $encrypted = base64_decode(substr($value, $prefix + 13));
            return Security::cipher($encrypted, $self->key);
        endif;
        return false;
    }
    public function expire($expires) {
        if(is_null($expires)):
            $expires = $this->expires;
        endif;
        $now = time();
        if(is_numeric($expires)):
            return $this->expires = $now + $expires;
        else:
            return $this->expires = strtotime($expires, $now);
        endif;
    }
}