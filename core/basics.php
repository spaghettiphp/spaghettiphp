<?php
/**
 *  O arquivo basics.php contém quatro classes básicas para o funcionamento
 *  do Spaghetti Framework. A classe Object é herdada por praticamente todas
 *  as outras classes existentes dentro do core do Spaghetti. A classe Spaghetti
 *  possui os métodos para importar os arquivos que serão solicitados ao longo
 *  da sua execução. A classe Config estabelece as configurações necessárias
 *  de banco de dados e de outras preferências da aplicação. A classe Error atua
 *  na manipulação de erros provenientes de qualquer lugar da aplicação.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Basics
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Object {
    public function log($message = "") {
        
    }
    public function error($type = "", $details = array()) {
        new Error($type, $details);
    }
    /**
     * O método Object::transmit() transforma um array de opções em respectivas
     * variáveis de classe que, portanto, são transmitidas para outra classe. Geralmente
     * é utilizada na mudança de camadas, entre modelos e controladores ou controladores
     * e visualizações.
     *
     * @param array $arr Array de opções
     * @return boolean
    */
    public function transmit($arr = array()) {
        foreach($arr as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }
}

class Spaghetti extends Object {
    /**
     * O método Spaghetti::import() faz a importação dos arquivos necessários
     * durante a execução do programa.
     *
     * @param string $type Contexto de onde será importado o arquivo
     * @param mixed $file Uma string com o nome do arquivo ou um array com nomes de arquivo
     * @param string $ext Extensão de arquivo do(s) arquivo(s) a ser(em) importado(s)
     * @param boolean $return Define se o metodo retorna o caminho para o arquivo ou a cópia em buffer
     * @return mixed Buffer do arquivo importado ou falso caso não consiga carregá-lo
    */
    static function import($type = "Core", $file = "", $ext = "php", $return = false) {
        $paths = array(
            "Core" => array(CORE),
            "App" => array(APP, LIB),
            "Lib" => array(LIB),
            "Webroot" => array(WEBROOT),
            "Model" => array(APP . DS . "models", LIB . DS . "models"),
            "Controller" => array(APP . DS . "controllers", LIB . DS . "controllers"),
            "View" => array(APP . DS . "views", LIB . DS . "views"),
            "Layout" => array(APP . DS . "layouts", LIB . DS . "layouts"),
            "Component" => array(APP . DS . "components", LIB . DS . "components"),
            "Helper" => array(APP . DS . "helpers", LIB . DS . "helpers"),
            "Filter" => array(APP . DS . "filters", LIB . DS . "filters")
        );
        foreach($paths[$type] as $path):
            if(is_array($file)):
                foreach($file as $file):
                    $include = Spaghetti::import($type, $file, $ext);
                endforeach;
                return $include;
            else:
                $file_path = $path . DS . "{$file}.{$ext}";
                if(file_exists($file_path)):
                    return $return ? $file_path : include($file_path);
                endif;
            endif;
        endforeach;
        return false;
    }
}

class Config extends Object {
    public $config = array();
    /**
     * O método Config::get_instance() retorna sempre o mesmo link de instância,
     * para que os métodos estáticos possam ser usados com características de
     * instâncias de objetos.
     *
     * @return resource
    */
    public function &get_instance() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] =& new Config();
        endif;
        return $instance[0];
    }
    /**
     * O método Config::read() retorna o valor de uma configuração da aplicação.
     *
     * @param string $key Nome da chave (variável) da configuração
     * @return mixed
     */
    static function read($key = "") {
        $self = self::get_instance();
        return $self->config[$key];
    }
    /**
     * O método Config::write() grava o valor de uma configuração da aplicação.
     *
     * @param string $key Nome da chave (variável) da configuração
     * @param string $value Valor da chave (variável) da configuração
     * @return mixed
     */
    static function write($key = "", $value = "") {
        $self = self::get_instance();
        $self->config[$key] = $value;
        return true;
    }
}

class Error extends Object {
    public function __construct($type = "", $details = array()) {
        pr($type);
        pr($details);
        die();
    }
}

?>