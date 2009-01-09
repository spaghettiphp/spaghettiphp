<?php
/**
 *  A classe Dispatcher é responsável por receber os parâmetros passados ao Spaghetti*
 *  através da URL, interpretá-los e direcioná-los para o respectivo controller.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Dispatcher
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

class Dispatcher extends Object {
    public $path = array();
    public $url = "";
    public function __construct($dispatch = true) {
        $this->parseUrl();
        if($dispatch) return $this->dispatch();
    }
    /**
     *  O método Dispatcher::parseUrl() faz a interpretação da URL, identificando
     *  prefixos, controller, action, id, extensão e parâmetros adicionais.
     * 
     *  @param string $url URL a ser interpretada
     *  @return array Array contendo a URL interpretada
     */
    public function parseUrl($url = null) {
        $here = Mapper::normalize(is_null($url) ? Mapper::here() : $url);
        $url = Mapper::getRoute($here);
        $prefixes = join("|", Mapper::getPrefixes());
        
        $parts = array("here", "prefix", "controller", "action", "id", "extension", "params");
        preg_match("/^\/(?:({$prefixes})(?:\/|(?!\w)))?(?:([a-z_-]*)\/?)?(?:([a-z_-]*)\/?)?(?:(\d*))?(?:\.([\w]+))?(?:\/?(.*))?/i", $url, $reg);
        foreach($parts as $k => $key) {
            $this->path[$key] = $reg[$k];
        }
        
        $this->path["namedParams"] = $this->path["params"] = array();
        foreach(split("/", $reg[6]) as $param):
            if(preg_match("/([^:]*):([^:]*)/", $param, $reg)):
                $this->path["namedParams"][$reg[1]] = urldecode($reg[2]);
            elseif($param != ""):
                $this->path["params"] []= urldecode($param);
            endif;
        endforeach;

        $this->path["here"] = $here;
        if(empty($this->path["action"])) $this->path["action"] = "index";
        if(!empty($this->path["prefix"])) $this->path["action"] = "{$this->path['prefix']}_{$this->path['action']}";
        if(empty($this->path["id"])) $this->path["id"] = null;
        if(empty($this->path["extension"])) $this->path["extension"] = Config::read("defaultExtension");
        
        return $this->path;
    }
    /**
     *  O método Dispatcher::dispatch() chama o controller e a action solicitados,
     *  além de inicializar componentes e renderizar a saída.
     * 
     *  @return mixed Instância do novo controller, ou falso em caso de erro
     */ 
    public function dispatch() {
        $controllerName = Inflector::camelize("{$this->path['controller']}_controller");
        $action = preg_replace("/-/", "_", $this->path["action"]);
        
        if($controller =& ClassRegistry::load($controllerName, "Controller")):
            if(!can_call_method($controller, $action) && !App::exists("View", preg_replace("/-/", "_", $this->path["controller"]) . "/{$action}", "p{$this->path['extension']}")):
                $this->error("missingAction", array("controller" => $controllerName, "action" => $action));
                return false;
            endif;
        else:
            if(App::exists("View", preg_replace("/-/", "_", $this->path["controller"]) . "/{$action}", "p{$this->path['extension']}")):
                $controller =& ClassRegistry::load("AppController", "Controller");
            else:
                $this->error("missingController", array("controller" => $controllerName));
                return false;
            endif;
        endif;

        $controller->params = $this->path;
        $controller->Component->initialize($controller);
        $controller->beforeFilter();
        $controller->Component->startup($controller);
        if(can_call_method($controller, $action)):
            $params = $this->path["params"];
            if(!is_null($this->path["id"])) $params = array_merge(array($this->path["id"]), $params);
            call_user_func_array(array(&$controller, $action), $params);
        endif;
        if($controller->autoRender):
            $controller->render();
        endif;
        $controller->Component->shutdown($controller);
        echo $controller->output;
        $controller->afterFilter();
        return $controller;
    }
}

?>