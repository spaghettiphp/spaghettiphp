<?php
/**
 *  Funcionalidades básicas do Spaghetti.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

/**
 *  Object é a classe herdada por todas as outras classes do Spaghetti, provendo
 *  funcionalidade básica para o framework.
 */
abstract class Object {
    private function log($message = "") {
        return $message;
    }
    private function error($type = "", $details = array()) {
        new Error($type, $details);
    }
    private function stop($status = null) {
        exit($status);
    }
}

/**
 *  App cuida de tarefas relativas a importação de arquivos dentro de uma aplicação
 *  do Spaghetti.
 */
class App extends Object {
    /**
     *  Importa um ou mais arquivos em uma aplicação.
     *
     *  @param string $type Tipo do arquivo a ser importado
     *  @param mixed $file String com o nome de um arquivo ou array com vários arquivos
     *  @param string $ext Extensão do(s) arquivo(s) a ser(em) importado(s)
     *  @return mixed Arquivo incluído ou falso em caso de erro
     */
    public static function import($type = "Core", $file = "", $ext = "php") {
        if(is_array($file)):
            foreach($file as $file):
                $include = self::import($type, $file, $ext);
            endforeach;
            return $include;
        else:
            if($file_path = self::path($type, $file, $ext)):
                return require $file_path;
            else:
                trigger_error("File {$file}.{$ext} doesn't exists in {$type}", E_USER_WARNING);
            endif;
        endif;
        return false;
    }
    /**
     *  Retorna o caminho completo de um arquivo dentro da aplicação.
     *
     *  @param string $type Tipo do arquivo a ser buscado
     *  @param string $file Nome do arquivo a ser buscado
     *  @param string $ext Extensão do arquivo a ser buscado
     *  @return mixed Caminho completo do arquivo ou falso caso não exista
     */
    public static function path($type = "Core", $file = "", $ext = "php") {
        $paths = array(
            "Core" => array(CORE),
            "App" => array(APP, LIB),
            "Lib" => array(LIB),

            "Model" => array(APP . DS . "models", LIB . DS . "models"),
            "Controller" => array(APP . DS . "controllers", LIB . DS . "controllers"),
            "View" => array(APP . DS . "views", LIB . DS . "views"),
            "Layout" => array(APP . DS . "layouts", LIB . DS . "layouts"),
            "Component" => array(APP . DS . "components", LIB . DS . "components"),
            "Helper" => array(APP . DS . "helpers", LIB . DS . "helpers"),

            "Script" => array(ROOT . DS . "script"),
            "Command" => array(ROOT. DS . "script" . DS . "commands"),
            "Task" => array(ROOT. DS . "script" . DS . "tasks"),
            "Template" => array(ROOT. DS . "script" . DS . "templates"),
        );
 
        foreach($paths[$type] as $path):
            $file_path = $path . DS . "{$file}.{$ext}";
            if(file_exists($file_path)):
                return $file_path;
            endif;
        endforeach;
        return false;
    }
}

/**
 *  Config é a classe que toma conta de todas as configurações necessárias para
 *  uma aplicação do Spaghetti.
 */
class Config extends Object {
    /**
     *  Definições de configurações.
     */
    private $config = array();
    public static function &getInstance() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] = new Config();
        endif;
        return $instance[0];
    }
    /**
     *  Retorna o valor de uma determinada chave de configuração.
     *
     *  @param string $key Nome da chave da configuração
     *  @return mixed Valor de configuração da respectiva chave
     */
    public static function read($key = "") {
        $self = self::getInstance();
        return $self->config[$key];
    }
    /**
     *  Grava o valor de uma configuração da aplicação para determinada chave.
     *
     *  @param string $key Nome da chave da configuração
     *  @param string $value Valor da chave da configuração
     *  @return boolean true
     */
    public static function write($key = "", $value = "") {
        $self = self::getInstance();
        $self->config[$key] = $value;
        return true;
    }
}

/**
 *  Error é a classe que trata os erros do Spaghetti, renderizando telas de erro
 *  amigáveis.
 */
class Error extends Object {
    public function __construct($type = "", $details = array()) {
        $view = new View;
        $filename = Inflector::underscore($type);
        if(!($viewFile = App::path("View", "errors/{$filename}.htm"))):
            $viewFile = App::path("View", "errors/missing_error.htm");
            $details = array("error" => $type);
        endif;
        echo $view->renderLayout($view->renderView($viewFile, array("details" => $details)), "error.htm");
        $this->stop();
    }
}

?>