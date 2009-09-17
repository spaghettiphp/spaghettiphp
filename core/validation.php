<?php
/**
 *  Validation é a classe responsável pela validação de dados dentro do Spaghetti*,
 *  provendo métodos para vários tipos de validação, e também com a possibilidade
 *  de criação de alguns métodos personalizados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Validation extends Object {
    /**
     *  Valida um valor alfanumérico (letras e números).
     *
     *  @param string $value Valor a ser validado
     *  @return boolean Verdadeiro caso o valor seja válido
     */
    public static function alphanumeric($value) {
        return preg_match("/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu", $value);
    }
    /**
     *  Valida um número ou comprimento de uma string que esteja entre dois outros
     *  valores especificados.
     *
     *  @param string $value Valor a ser validado
     *  @param integer $min Valor mínimo
     *  @param integer $max Valor máximo
     *  @return boolean Verdadeiro caso o valor seja válido
     */
    public static function between($value, $min, $max) {
        if(!is_numeric($value)):
            $value = strlen($value);
        endif;
        return $value >= $min && $value <= $max;
    }
    public static function blank() {
        
    }
    /**
     *  Short description.
     *
     *  @param string $value
     *  @return boolean
     */
    public static function boolean($value) {
        $boolean = array(0, 1, '0', '1', true, false);
        return in_array($value, $boolean, true);
    }
    public static function creditCard() {
        
    }
    public static function comparison() {
        
    }
    public static function date() {
        
    }
    /**
     *  Short description.
     *
     *  @param string $value
     *  @param integer $places
     *  @return boolean
     */
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
    /**
     *  Short description.
     *
     *  @param string $value
     *  @param integer $length
     *  @return boolean
     */
    public static function minLength($value, $length) {
        $valueLength = strlen($value);
        return $valueLength >= $length;
    }
    /**
     *  Short description.
     *
     *  @param string $value
     *  @param integer $length
     *  @return boolean
     */
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
    /**
     *  Short description.
     *
     *  @param string $value
     *  @return boolean
     */
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