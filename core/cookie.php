<?php
/**
 *  Cookie cuida da criação e leitura de cookies para o Spaghetti*, levando em conta
 *  aspectos de segurança, encriptando todos os cookies criados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Cookie extends Object {
    /**
      *  Armazena a data de expiração do cookie.
      */
    public $expires;
    /**
      *  Caminho para o qual o cookie está disponível.
      */
    public $path = "/";
    /**
      *  Domínio para ao qual o cookie está disponível.
      */
    public $domain = "";
    /**
      *  Define um cookie seguro.
      */
    public $secure = false;
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
    /**
     *  Retorna uma única instância (Singleton) da classe solicitada.
     *
     *  @return object Objeto da classe utilizada
     */
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
        $path = Mapper::base() . $self->path;
        return setcookie("{$self->name}[{$name}]", "", time() - 42000, $path, $self->domain, $self->secure);
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
        $expires = $self->expire($expires);
        $path = Mapper::normalize(Mapper::base() . $self->path);
        return setcookie("{$self->name}[{$name}]", self::encrypt($value), $expires, $path, $self->domain, $self->secure);
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
    /**
     *  Define o tempo de duração de um cookie.
     *
     *  @param mixed $expires Tempo de duração do cookie
     *  @return mixed Data de expiração do cookie
     */
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