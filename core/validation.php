<?php
/**
 *  Short description.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Validation extends Object {
    public static function alphanumeric($value) {
        return preg_match("/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu", $value);
    }
    public static function between() {
        
    }
    public static function blank() {
        
    }
    public static function boolean() {
        
    }
    public static function creditCard() {
        
    }
    public static function comparison() {
        
    }
    public static function date() {
        
    }
    public static function decimal($value, $places = null) {
        if(is_null($places)):
            $regex = "/^[+-]?[\d]+\.[\d]+([eE][+-]?[\d]+)?$/";
        else:
            $regex = "/^[+-]?[\d]+\.[\d]{" . $places . "}$/";
        endif;
        return preg_match($regex, $value);
    }
    public static function email() {
        
    }
    public static function equal() {
        
    }
    public static function file() {
        // extension?
    }
    public static function ip() {
        
    }
    public static function minLength($value, $length) {
        $valueLength = strlen($value);
        return $valueLength >= $length;
    }
    public static function maxLength($value, $length) {
        $valueLength = strlen($value);
        return $valueLength <= $length;
    }
    public static function money() {
        
    }
    public static function multiple() {
        
    }
    public static function inList() {
        
    }
    public static function numeric($value) {
        return is_numeric($value);
    }
    public static function notEmpty() {
        
    }
    public static function range() {
        
    }
    public static function time() {
        
    }
    public static function url() {
        
    }
}

?>