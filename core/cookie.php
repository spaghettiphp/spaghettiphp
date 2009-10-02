<?php
/**
 *  Short description.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Cookie extends Object {
    /**
      *  Short description.
      */
    public $expires;
    /**
      *  Short description.
      */
    public $domain;
    /**
      *  Short description.
      */
    public $secure;
    /**
      *  Short description.
      */
    public $path;
    /**
      *  Short description.
      */
    private $values;
    /**
      *  Instância da classe.
      */
    public static $instance;
    public $key;
    public $name = "Spaghetti";
    
    public function __construct() {
        $this->key = Config::read("securitySalt");
    }
    public static function getInstance() {
        if(!isset(self::$instance)):
            $c = __CLASS__;
            self::$instance = new $c;
        endif;
        return self::$instance;
    }
    /**
      *  Short description.
      *
      *  @param string $name
      *  @return boolean
      */
    public static function delete($name) {
        
    }
    public static function destroy() {
        
    }
    /**
      *  Lê o valor de um cookie.
      *
      *  @param string $name Nome do cookie a ser lido
      *  @return string Valor do cookie
      */
    public static function read($name) {
        $self = self::getInstance();
        return self::decrypt($_COOKIE[$self->name][$name]);
    }
    /**
      *  Salva um cookie.
      *
      *  @param string $name Nome do cookie a ser salvo
      *  @param string $value Valor do cookie
      *  @return boolean Verdadeiro se o cookie foi salvo
      */
    public static function write($name, $value) {
        $self = self::getInstance();
        return setcookie("{$self->name}[{$name}]", self::encrypt($value));
    }
    /**
      *  Encripta o valor de um cookie.
      *
      *  @param string $value Valor a ser encriptado
      *  @return string Valor encriptado
      */
    public static function encrypt($value) {
        $self = self::getInstance();
        $encripted = base64_encode(Security::cipher($value, $self->key));
        return "U3BhZ2hldHRp.{$encripted}";
    }
    /**
      *  Decripta o valor de um cookie.
      *
      *  @param string $value Valor encriptado
      *  @return string Valor decriptado
      */
    public static function decrypt($value) {
        $self = self::getInstance();
        $prefix = strpos($value, "U3BhZ2hldHRp.");
        if($prefix !== false):
            $encrypted = base64_decode(substr($value, $prefix + 13));
            return Security::cipher($encrypted, $self->key);
        endif;
        return false;
    }
}

?>