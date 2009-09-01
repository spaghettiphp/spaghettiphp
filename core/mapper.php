<?php
/**
 *  Mapper é o responsável por cuidar de URLs e roteamento dentro do Spaghetti.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Mapper extends Object {
    /**
     *  Definições de prefixos.
     */
    public $prefixes = array();
    /**
     *  Definição de rotas.
     */
    public $routes = array();
    /**
     *  URL atual da aplicação.
     */
    private $here = null;
    /**
     *  URL base da aplicação.
     */
    private $base = null;
    /**
     *  Controller padrão da aplicação.
     */
    public $root = null;
    /**
     *  Define a URL base e URL atual da aplicação.
     *
     *  @return void
     */
    public function __construct() {
        if(is_null($this->base)):
            $this->base = dirname($_SERVER["PHP_SELF"]);
            while(in_array(basename($this->base), array("app", "core", "tests", "webroot"))):
                $this->base = dirname($this->base);
            endwhile;
            if($this->base == DS || $this->base == "."):
                $this->base = "/";
            endif;
        endif;
        if(is_null($this->here)):
            $start = strlen($this->base);
            $this->here = self::normalize(substr($_SERVER["REQUEST_URI"], $start));
        endif;
    }
    /**
     *  Retorna uma única instância (Singleton) da classe solicitada.
     *
     *  @staticvar object $instance Objeto a ser verificado
     *  @return object Objeto da classe utilizada
     */
    public static function &getInstance() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] = new Mapper();
        endif;
        return $instance[0];
    }
    /**
     *  Normaliza uma URL, removendo barras duplicadas ou no final de strings e
     *  adicionando uma barra inicial quando necessário.
     *
     *  @param string $url URL a ser normalizada
     *  @return string URL normalizada
     */
    public static function normalize($url = "") {
        if(preg_match("/^[a-z]+:/", $url)):
            return $url;
        endif;
        $url = "/" . $url;
        while(strpos($url, "//") !== false):
            $url = str_replace("//", "/", $url);
        endwhile;
        $url = preg_replace("/\/$/", "", $url);
        if(empty($url)):
            $url = "/";
        endif;
        return $url;
    }
    /**
     *  Gera uma URL, levando em consideração o local atual da aplicação.
     *
     *  @param string $path Caminho relativo ou URL absoluta
     *  @param bool $full URL completa (true) ou apenas o caminho
     *  @return string URL gerada para a aplicação
     */
    public static function url($path = null, $full = false) {
        if(preg_match("/^[a-z]+:/", $path)):
            return $path;
        elseif(substr($path, 0, 1) == "/"):
            $url = self::base() . $path;
        else:
            $url = self::base() . self::here() . "/" . $path;
        endif;
        $url = self::normalize($url);
        return $full ? BASE_URL . $url : $url;
    }
    /**
     *  Short Description
     *
     *  @param string $url description
     *  @param string $route description
     *  @return mixed description
     */
    public static function connect($url = null, $route = null) {
        if(is_array($url)):
            foreach($url as $key => $value):
                self::connect($key, $value);
            endforeach;
            return true;
        elseif(!is_null($url)):
            $self = self::getInstance();
            $url = self::normalize($url);
            return $self->routes[$url] = rtrim($route, "/");
        endif;
        return false;
    }
    /**
     *  Short Description
     *
     *  @param string $url description
     *  @return true
     */
    public static function disconnect($url = "") {
        $self = self::getInstance();
        $url = rtrim($url, "/");
        unset($self->routes[$url]);
        return true;
    }
    /**
     *  Short Description
     *
     *  @param string $url description
     *  @return string description
     */
    public static function getRoute($url) {
        $self = self::getInstance();
        foreach($self->routes as $map => $route):
            $map = "/^" . str_replace(array("/", ":any", ":fragment", ":num"), array("\/", "(.+)", "([^\/]+)", "([0-9]+)"), $map) . "\/?$/";
            $newUrl = preg_replace($map, $route, $url);
            if($newUrl != $url):
                $url = $newUrl;
                break;
            endif;
        endforeach;
        return self::normalize($url);
    }
    /**
     *  Define o controller padrão da aplicação
     *
     *  @param string $controller Controller a ser definido como padrão
     *  @return true
     */
    public static function root($controller = "") {
        $self = self::getInstance();
        $self->root = $controller;
        return true;
    }
    /**
     *  Short Description
     *
     *  @param string $prefix description
     *  @return true
     */
    public static function prefix($prefix = "") {
        $self = self::getInstance();
        if(is_array($prefix)) $prefixes = $prefix;
        else $prefixes = func_get_args();
        foreach($prefixes as $prefix):
            $self->prefixes []= $prefix;
        endforeach;
        return true;
    }
    /**
     *  Remove um prefixo da lista
     *
     *  @param string $prefix Prefixo a ser removido
     *  @return true
     */
    public static function unsetPrefix($prefix = "") {
        $self = self::getInstance();
        unset($self->prefixes[$prefix]);
        return true;
    }
    /**
     *  Retorna uma lista com todos os prefixos definidos pela aplicação.
     *
     *  @return array Lista de prefixos
     */
    public static function getPrefixes() {
        $self = self::getInstance();
        return $self->prefixes;
    }
    /**
     *  Getter para Mapper::here
     *
     *  @return string Valor de Mapper:here
     */
    public static function here() {
        $self = self::getInstance();
        return $self->here;
    }
    /**
     *  Getter para Mapper::base
     *
     *  @return string Valor de Mapper::base
     */
    public static function base() {
        $self = self::getInstance();
        return $self->base;
    }
    /**
     *  Getter para Mapper::root
     *
     *  @return string Controller padrão da aplicação
     */
    public static function getRoot() {
        $self = self::getInstance();
        return $self->root;
    }
}

?>