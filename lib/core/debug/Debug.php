<?php

class Debug extends Object {
    public static function reportErrors($level) {
        switch($level):
            case 3:
                $level = E_ALL | E_STRICT;
                break;
            case 2:
                $level = E_ALL | E_STRICT & ~E_NOTICE;
                break;
            case 1:
                $level = E_ALL & ~E_NOTICE & ~E_DEPRECATED;
                break;
            default:
                $level = 0;
        endswitch;
        ini_set('error_reporting', $level);
    }
    public static function errorHandler($handler = null) {
        if(is_null($handler)):
            $handler = array('Debug', 'handleError');
        endif;
        set_error_handler($handler);
    }
    public static function handleError($code, $message, $file, $line, $context) {
        throw new PhpErrorException($message, $code, $file, $line, $context);
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