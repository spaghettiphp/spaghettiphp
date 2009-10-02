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
      *  @return array
      */
    private function readValues() {
        "U3BhZ2hldHRpKg==";
    }
    /**
      *  Short description.
      *
      *  @param string $name
      *  @return boolean
      */
    public static function delete($name) {
        
    }
    /**
      *  Short description.
      *
      *  @param string $name
      *  @return string
      */
    public static function read($name) {
        
    }
    /**
      *  Short description.
      *
      *  @param string $name
      *  @param string $value
      *  @return boolean
      */
    public static function write($name, $value) {
        
    }
    public static function encrypt($value) {
        $self = self::getInstance();
        return base64_encode(Security::cipher($value, $self->key));
    }
    public static function decrypt($value) {
        
    }
}

?>