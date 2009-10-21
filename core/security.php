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
      *  @param string $text Valor a ser encriptado/decriptado
      *  @param string $key Chage a ser usada para encriptar/decriptar o valor
      *  @return string Valor encriptado/decriptado
      *  @copyright Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
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
    /**
      *  Cria um hash de uma string usando o método especificado.
      *
      *  @param string $text Texto a ser hasheado
      *  @param string $hash Método de hashing
      *  @param mixed $salt Salt a ser usado
      *  @return string Hash do valor
      */
    public static function hash($text, $hash = null, $salt = false) {
        if($salt):
            if(is_string($salt)):
                $text = $salt . $text;
            else:
                $text = Config::read("securitySalt") . $text;
            endif;
        endif;
        switch($hash):
            case "md5":
                return md5($text);
            case "sha256":
                return bin2hex(mhash(MHASH_SHA256, $text));
            case "sha1":
            default:
                return sha1($text);
        endswitch;
        return false;
    }
}

?>