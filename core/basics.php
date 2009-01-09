<?php
/**
 *  O arquivo basics.php contém algumas classes básicas para o funcionamento do
 *  Spaghetti, como classes base e importação de arquivos.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Basics
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 */


/**
 *  Object é a classe básica do Spaghetti, fornecendo funcionalidade básica para
 *  todas as outras classes do framework.
 */
class Object {
    public function log($message = "") {
        return $message;
    }
    public function error($type = "", $details = array()) {
        new Error($type, $details);
    }
}

/**
 *  App é a classe que cuida da aplicação do Spaghetti, fazendo a verificação e
 *  inclusão de arquivos.
 */
class App extends Object {
    /**
     *  App::import() faz a importação dos arquivos necessários
     *  durante a execução do script.
     *
     *  @param string $type Contexto de onde será importado o arquivo
     *  @param mixed $file Uma string com o nome do arquivo ou um array com nomes de arquivo
     *  @param string $ext Extensão de arquivo do(s) arquivo(s) a ser(em) importado(s)
     *  @return mixed Buffer do arquivo importado ou falso caso não consiga carregá-lo
    */
    static function import($type = "Core", $file = "", $ext = "php") {
        if(is_array($file)):
            foreach($file as $file):
                $include = App::import($type, $file, $ext);
            endforeach;
            return $include;
        else:
            if($file_path = App::exists($type, $file, $ext)):
                return include $file_path;
            endif;
        endif;
        return false;
    }
    /**
     *  App::exists verifica se um arquivo existe dentro de uma aplicação do Spaghetti;
     *
     *  @param string $type Contexto de onde reside o arquivo
     *  @param string $file Nome do arquivo a ser verificado
     *  @param string $ext Extensão do arquivo a ser verificado
     *  @return mixed Caminho completo do arquivo ou falso caso o mesmo não exista
     */
    public function exists($type = "Core", $file = "", $ext = "php") {
        $paths = array(
            // Diretórios do Core
            "Core" => array(CORE),
            "App" => array(APP, LIB),
            "Lib" => array(LIB),

            // Diretórios da Aplicação
            "Webroot" => array(WEBROOT),
            "Model" => array(APP . DS . "models", LIB . DS . "models"),
            "Controller" => array(APP . DS . "controllers", LIB . DS . "controllers"),
            "View" => array(APP . DS . "views", LIB . DS . "views"),
            "Layout" => array(APP . DS . "layouts", LIB . DS . "layouts"),
            "Component" => array(APP . DS . "components", LIB . DS . "components"),
            "Helper" => array(APP . DS . "helpers", LIB . DS . "helpers"),

            // Diretórios do Shell
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
    public $config = array();
    public function &getInstance() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] =& new Config();
        endif;
        return $instance[0];
    }
    /**
     *  Config::read() retorna o valor de uma determinada chave de configuração.
     *
     *  @param string $key Nome da chave da configuração
     *  @return mixed Valor de configuração da respectiva chave
     */
    static function read($key = "") {
        $self = self::getInstance();
        return $self->config[$key];
    }
    /**
     *  Config::write() grava o valor de uma configuração da aplicação para determinada
     *  chave.
     *
     *  @param string $key Nome da chave da configuração
     *  @param string $value Valor da chave da configuração
     *  @return boolean true
     */
    static function write($key = "", $value = "") {
        $self = self::getInstance();
        $self->config[$key] = $value;
        return true;
    }
}

/**
 *  Error é a classe que trata os erros do Spaghetti, renderizando telas de erro
 *  amigáveis através do sistema de Views.
 */
class Error extends Object {
    public function __construct($type = "", $details = array()) {
        $view = new View;
        $filename = Inflector::underscore($type);
        if(!($viewFile = App::exists("View", "errors/{$filename}", "phtm"))):
            $viewFile = App::exists("View", "errors/missing_error", "phtm");
            $details = array("error" => $type);
        endif;
        echo $view->renderLayout($view->renderView($viewFile, array("details" => $details)), "error.phtm");
        die();
    }
}

?>