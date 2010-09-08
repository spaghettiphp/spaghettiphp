<?php

class Config {
    protected static $config = array();
    
    public static function read($key) {
        if(array_key_exists($key, self::$config)):
            return self::$config[$key];
        endif;
        
        return null;
    }
    public static function write($key, $value) {
        self::$config[$key] = $value;
    }
}