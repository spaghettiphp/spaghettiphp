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
    public static $patterns = array(
        "ip" => "(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])",
        "hostname" => "(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)"
    );
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
    /**
      *  Valida se um valor é vazio.
      *
      *  @param string $value Valor a ser validado
      *  @return boolean Verdadeiro caso o valor seja válido
      */
    public static function blank($value) {
        return !preg_match("/[^\s]/", $value);
    }
    /**
     *  Valida um valor booleano (true, false, 0 ou 1).
     *
     *  @param string $value Valor a ser validado
     *  @return boolean Verdadeiro caso o valor seja válido
     */
    public static function boolean($value) {
        $boolean = array(0, 1, '0', '1', true, false);
        return in_array($value, $boolean, true);
    }
    /**
      *  Valida um número de cartão de crédito.
      *
      *  @param string $value Valor a ser validado
      *  @return boolean Verdadeiro caso o valor seja válido
      */
    public static function creditCard($value) {
        return preg_match("/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/", $value);
    }
    /**
     *  Valida valores através de comparação.
     *
     *  @param string $value1 Primeiro valor a ser comparado
     *  @param string $operator Operador usado para comparar os valores
     *  @param string $value2 Segundo valor a ser comparado
     *  @return boolean Resultado da comparação
     */
    public static function comparison($value1, $operator, $value2) {
        switch($operator):
            case ">":
            case "greater":
                return $value1 > $value2;
            case "<":
            case "less":
                return $value1 < $value2;
            case ">=":
            case "greaterorequal":
                return $value1 >= $value2;
            case "<=":
            case "lessorequal":
                return $value1 <= $value2;
            case "==":
            case "equal":
                return $value1 == $value2;
            case "!=":
            case "notequal":
                return $value1 != $value2;
        endswitch;
        return false;
    }
    /**
      *  Valida um valor de acordo com uma expressão regular personalizada.
      *
      *  @param string $value Valor a ser validado
      *  @param string $regex Expressão regular a ser usada
      *  @return boolean Verdadeiro caso o valor seja válido
      */
    public function regex($value, $regex) {
        return preg_match($value, $regex);
    }
    /**
      *  Valida uma data no formato d/m/y.
      *
      *  @param string $value Valor a ser validado
      *  @return boolean Verdadeiro caso o valor seja válido
      */
    public static function date($value) {
        $regex = "%^(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)(\\/|-|\\.|\\x20)(?:0?[1,3-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:29(\\/|-|\\.|\\x20)0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\\d|2[0-8])(\\/|-|\\.|\\x20)(?:(?:0?[1-9])|(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%";
        return preg_match($regex, $value);
    }
    /**
     *  Valida um número decimal.
     *
     *  @param string $value Valor a ser validado
     *  @param integer $places Número de casas decimais
     *  @return boolean Verdadeiro caso o valor seja válido
     */
    public static function decimal($value, $places = null) {
        if(is_null($places)):
            $regex = "/^[+-]?[\d]+\.[\d]+([eE][+-]?[\d]+)?$/";
        else:
            $regex = "/^[+-]?[\d]+\.[\d]{" . $places . "}$/";
        endif;
        return preg_match($regex, $value);
    }
    /**
      *  Valida um endereço de e-mail.
      *
      *  @param string $value Valor a ser validado
      *  @param boolean $checkHost Verdadeiro para chegar o host
      *  @return boolean Verdadeiro caso o valor seja válido
      */
    public static function email($value, $checkHost = false) {
        $match = preg_match("/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@" . self::$patterns["hostname"] . "$/i", $value);
        if($match && $checkHost):
            preg_match("/@(" . self::$patterns["hostname"] . ")$/i", $value, $reg);
            $host = gethostbynamel($reg[1]);
            return is_array($host);
        endif;
        return $match;
    }
    /**
      *  Valida se um valor é igual a outro valor pré-definido.
      *
      *  @param string $value Valor a ser validado
      *  @param string $compare Valor a ser comparado
      *  @return boolean Verdadeiro caso o valor seja válido
      */
    public static function equal($value, $compare) {
        return $value === $compare;
    }
    /**
      *  Valida se o valor é um IP válido.
      *
      *  @param string $value Valor a ser validado
      *  @return boolean Verdadeiro caso o valor seja válido.
      */
    public static function ip($value) {
        return preg_match("/^" . self::$patterns["ip"] . "$/", $value);
    }
    /**
     *  Valida se um valor tem um tamanho mínimo.
     *
     *  @param string $value Valor a ser validado
     *  @param integer $length Tamanho mínimo do valor
     *  @return boolean Verdadeiro caso o valor seja válido
     */
    public static function minLength($value, $length) {
        $valueLength = strlen($value);
        return $valueLength >= $length;
    }
    /**
     *  Valida se um valor tem um tamanho máximo.
     *
     *  @param string $value Valor a ser validado
     *  @param integer $length Tamanho máximo do valor
     *  @return boolean Verdadeiro caso o valor seja válido
     */
    public static function maxLength($value, $length) {
        $valueLength = strlen($value);
        return $valueLength <= $length;
    }
    /**
     *  Valida um valor de múltipla escolha.
     *
     *  @param array $values Valores a serem validados
     *  @param array $list Lista contendo valores válidos
     *  @param integer $min Mínimo de ocorrências de escolhas
     *  @param integer $max Máximo de ocorrências de escolhas
     *  @return boolean Verdadeiro caso os valores sejam válidos
     */
    public static function multiple($values, $list, $min = null, $max = null) {
        $values = array_filter($values);
        if(empty($values)):
            return false;
        elseif(!is_null($min) && count($values) < $min):
            return false;
        elseif(!is_null($max) && count($values) > $max):
            return false;
        else:
            foreach(array_keys($values) as $value):
                if(!in_array($value, $list)):
                    return false;
                endif;
            endforeach;
        endif;
        return true;
    }
    /**
     *  Valida se um valor pertence a uma lista pré-definida.
     *
     *  @param string $value Valor a ser validado
     *  @param array $list Lista contendo valores válidos
     *  @return boolean Verdadeiro caso o valor seja válido
     */
    public static function inList($value, $list) {
        return in_array($value, $list);
    }
    /**
     *  Valida um valor numérico.
     *
     *  @param string $value Valor a ser validado
     *  @return boolean Verdadeiro caso o valor seja válido
     */
    public static function numeric($value) {
        return is_numeric($value);
    }
    /**
     *  Valida um valor não-vazio.
     *
     *  @param string $value Valor a ser validado
     *  @return boolean Verdadeiro caso o valor seja válido
     */
    public static function notEmpty($value) {
        return preg_match("/[^\s]+/m", $value);
    }
    /**
      *  Valida se um valor está dentro de uma faixa especificada.
      *
      *  @param integer $value Valor a ser validado
      *  @param integer $lower Menor valor da faixa
      *  @param integer $upper Maior valor da faixa
      *  @return boolean Verdadeiro caso o valor seja válido
      */
    public static function range($value, $lower = null, $upper = null) {
        if(is_numeric($value)):
            if(!is_null($lower) || !is_null($upper)):
                $checkLower = $checkUpper = true;
                if(!is_null($lower)):
                    $checkLower = $value > $lower;
                endif;
                if(!is_null($upper)):
                    $checkUpper = $value < $upper;
                endif;
            else:
                return is_finite($value);
            endif;
            return $checkLower && $checkUpper;
        endif;
        return false;
    }
    /**
      *  Valida se o valor passado é um horário.
      *
      *  @param string $value Valor a ser validado
      *  @return boolean Verdadeiro caso o valor seja válido
      */
    public static function time($value) {
        $regex = "/^([01]\d|2[0-3])(:[0-5]\d){1,2}$"
               . "|^(0?[1-9]|1[0-2])(:[0-5]\d){1,2}\s?[AaPp]m$/";
        return preg_match($regex, $value);
    }
    /**
      *  Valida uma URL válida.
      *
      *  @param string $value Valor a ser validado
      *  @param boolean $strict Limitar a URL a protocolos válidos
      *  @return boolean Verdadeiro caso o valor seja válido
      */
    public static function url($value, $strict = false) {
        $chars = '([' . preg_quote('!"$&\'()*+,-.@_:;=') . '\/0-9a-z]|(\%[0-9a-f]{2}))';
        $regex = "(?:(?:https?|ftps?|file|news|gopher)://)?"
               . "(?:" . self::$patterns["ip"] . "|" . self::$patterns["hostname"] . ")"
               . "(?::[1-9][0-9]{0,3})?"
               . "(?:/?|/{$chars}*)?"
               . "(?:\?{$chars}*)?"
               . "(?:#{$chars}*)?";
        return preg_match("%^{$regex}$%i", $value);
    }
}

?>