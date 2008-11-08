<?php
/**
 *  O arquivo basics.php contщm quatro classes bсsicas para o funcionamento
 *  do Spaghetti Framework. A classe Object щ herdada por praticamente todas
 *  as outras classes existentes dentro do core do Spaghetti. A classe Spaghetti
 *  possui os mщtodos para importar os arquivos que serуo solicitados ao longo
 *  da sua execuчуo. A classe Config estabelece as configuraчѕes necessсrias
 *  de banco de dados e de outras preferъncias da aplicaчуo. A classe Error atua
 *  na manipulaчуo de erros provenientes de qualquer lugar da aplicaчуo.
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
     * O mщtodo Object::transmit() transforma um array de opчѕes em respectivas
     * variсveis de classe que, portanto, sуo transmitidas para outra classe. Geralmente
     * щ utilizada na mudanчa de camadas, entre modelos e controladores ou controladores
     * e visualizaчѕes.
     *
     * @param array $arr Array de opчѕes
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
     * O mщtodo Spaghetti::import() faz a importaчуo dos arquivos necessсrios
     * durante a execuчуo do programa.
     *
     * @param string $type Contexto de onde serс importado o arquivo
     * @param mixed $file Uma string com o nome do arquivo ou um array com nomes de arquivo
     * @param string $ext Extensуo de arquivo do(s) arquivo(s) a ser(em) importado(s)
     * @param boolean $return Define se o metodo retorna o caminho para o arquivo ou a cѓpia em buffer
     * @return mixed Buffer do arquivo importado ou falso caso nуo consiga carregс-lo
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
     * O mщtodo Config::get_instance() retorna sempre o mesmo link de instтncia,
     * para que os mщtodos estсticos possam ser usados com caracterэsticas de
     * instтncias de objetos.
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
     * O mщtodo Config::read() retorna o valor de uma configuraчуo da aplicaчуo.
     *
     * @param string $key Nome da chave (variсvel) da configuraчуo
     * @return mixed
     */
    static function read($key = "") {
        $self = self::get_instance();
        return $self->config[$key];
    }
    /**
     * O mщtodo Config::write() grava o valor de uma configuraчуo da aplicaчуo.
     *
     * @param string $key Nome da chave (variсvel) da configuraчуo
     * @param string $value Valor da chave (variсvel) da configuraчуo
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