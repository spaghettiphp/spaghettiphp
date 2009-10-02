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
      *  Chave a ser usada na encriptação/decriptação do cookie.
      */
    public $key;
    /**
      *  Namespace do cookie.
      */
    public $name = "SpaghettiCookie";
    /**
      *  Instância da classe.
      */
    public static $instance;
    
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
      *  Setters para Cookie.
      *
      *  @param string $key Propriedade a ser definida
      *  @param mixed $value Valor da propriedade
      *  @return Verdadeiro caso a propriedade tenha sido definida
      */
    public static function set($key, $value) {
        $self = self::getInstance();
        if(isset($self->$key)):
            $self->$key = $value;
            return true;
        endif;
        return false;
    }
    /**
      *  Getters para Cookie.
      *
      *  @param string $key Propriedade a ser retornada
      *  @return mixed Valor da propriedade
      */
    public static function get($key) {
        $self = self::getInstance();
        if(isset($self->$key)):
            return $self->$key;
        endif;
        return null;
    }
    /**
      *  Apaga um cookie.
      *
      *  @param string $name Nome do cookie a ser apagado
      *  @return boolean Verdadeiro caso o cookie tenha sido apagado
      */
    public static function delete($name) {
        $self = self::getInstance();
        return setcookie("{$self->name}[{$name}]", "", time() - 42000);
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
    public static function write($name, $value, $expires = null) {
        $self = self::getInstance();
        return setcookie("{$self->name}[{$name}]", self::encrypt($value), $self->expire($expires));
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
    public function expire($expires) {
        if(is_null($expires)):
            $expires = $this->expires;
        endif;
        $now = time();
        if(is_numeric($expires)):
            return $this->expires = $now + $expires;
        else:
            return $this->expires = strtotime($expires, $now);
        endif;
    }
}

?>