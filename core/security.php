<?php
/**
 *  Security cuida de vários aspectos relacionados à segurança de sua aplicação,
 *  fazendo encriptação/decriptação e hashing de dados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Security extends Object {
    /**
      *  Encripta/decripta um valor usando a chave especificada.
      *
      *  @copyright Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
      *  @param string $text Valor a ser encriptado/decriptado
      *  @param string $key Chage a ser usada para encriptar/decriptar o valor
      *  @return string Valor encriptado/decriptado
      */
    public static function cipher($text, $key) {
        if(empty($key)):
            trigger_error("You cannot use an empty key for Security::cipher()", E_USER_WARNING);
            return null;
        endif;
        if (!defined("CIPHER_SEED")):
            define("CIPHER_SEED", "76859309657453542496749683645");
        endif;
        srand(CIPHER_SEED);
        $output = "";
        for($i = 0; $i < strlen($text); $i++):
            for($j = 0; $j < ord(substr($key, $i % strlen($key), 1)); $j++):
                rand(0, 255);
            endfor;
            $mask = rand(0, 255);
            $output .= chr(ord(substr($text, $i, 1)) ^ $mask);
        endfor;
        return $output;
    }
}

?>