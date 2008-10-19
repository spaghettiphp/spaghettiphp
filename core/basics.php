<?php
/**
 *  Put description here
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
    public function transmit($arr = array()) {
        foreach($arr as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }
}

class Spaghetti extends Object {
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
    public function &get_instance() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] =& new Config();
        endif;
        return $instance[0];
    }
    static function read($key = "") {
        $self = self::get_instance();
        return $self->config[$key];
    }
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
