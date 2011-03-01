<?php

class Debug {
    public static function reportErrors($level) {
        $levels = array(
            0 => 0,
            1 => E_ALL & ~E_NOTICE & ~E_DEPRECATED,
            2 => E_ALL | E_STRICT & ~E_NOTICE,
            3 => E_ALL | E_STRICT
        );
        ini_set('error_reporting', $levels[$level]);
    }

    public static function errorHandler($handler = null) {
        if(is_null($handler)) {
            $handler = array('Debug', 'handleError');
        }

        set_error_handler($handler);
    }

    public static function handleError($code, $message, $file, $line) {
        throw new ErrorException($message, 0, $code, $file, $line);
    }

    public static function pr($data) {
        echo '<pre>' . print_r($data, true) . '</pre>';
    }

    public static function dump($data) {
        self::pr(var_export($data, true));
    }

    public static function trace() {
        return debug_backtrace();
    }
}

function pr($data) {
    Debug::pr($data);
}

function dump($data) {
    Debug::dump($data);
}