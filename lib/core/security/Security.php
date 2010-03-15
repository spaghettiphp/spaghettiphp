<?php

class Security extends Object {
    public static function cipher($text, $key) {
        if(empty($key)):
            trigger_error('You cannot use an empty key for Security::cipher()', E_USER_WARNING);
            return null;
        endif;
        if (!defined('CIPHER_SEED')):
            define('CIPHER_SEED', '76859309657453542496749683645');
        endif;
        srand(CIPHER_SEED);
        $output = '';
        for($i = 0; $i < strlen($text); $i++):
            for($j = 0; $j < ord(substr($key, $i % strlen($key), 1)); $j++):
                rand(0, 255);
            endfor;
            $mask = rand(0, 255);
            $output .= chr(ord(substr($text, $i, 1)) ^ $mask);
        endfor;
        return $output;
    }
    public static function hash($text, $hash = null, $salt = false) {
        if($salt):
            if(is_string($salt)):
                $text = $salt . $text;
            else:
                $text = Config::read('securitySalt') . $text;
            endif;
        endif;
        switch($hash):
            case 'md5':
                return md5($text);
            case 'sha256':
                return bin2hex(mhash(MHASH_SHA256, $text));
            case 'sha1':
            default:
                return sha1($text);
        endswitch;
        return false;
    }
    public static function token(){
        return ($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']) . Session::id();
    }
}