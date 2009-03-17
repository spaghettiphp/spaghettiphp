<?php
/**
 *  Dispatcher é o responsável por receber os parâmetros passados ao Spaghetti*
 *  através da URL, interpretá-los e direcioná-los para o respectivo controller.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Dispatcher extends Object {
    /**
     *  URL interpretada por Dispatcher::parseUrl.
     */
    public $path = array();
    /**
     *  URL recebida através de Mapper::here.
     */
    public $url = "";
    
    public function __construct($dispatch = true) {
        $this->parseUrl();
        if($dispatch) return $this->dispatch();
    }
    /**
     *  Faz a interpretação da URL, identificando as partes da URL.
     * 
     *  @param string $url URL a ser interpretada
     *  @return array URL interpretada
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
        if(empty($this->path["controller"])) $this->path["controller"] = Mapper::getRoot();
        if(empty($this->path["action"])) $this->path["action"] = "index";
        if(!empty($this->path["prefix"])) $this->path["action"] = "{$this->path['prefix']}_{$this->path['action']}";
        if(empty($this->path["id"])) $this->path["id"] = null;
        if(empty($this->path["extension"])) $this->path["extension"] = Config::read("defaultExtension");
        
        return $this->path;
    }
    /**
     *  Chama o controller e a action solicitadas pela URL.
     * 
     *  @return mixed Instância do novo controller ou falso em caso de erro
     */ 
    public function dispatch() {
        $controllerName = Inflector::camelize("{$this->path['controller']}_controller");
        $action = Inflector::hyphenToUnderscore($this->path["action"]);
        $controllerFile = Inflector::hyphenToUnderscore($this->path["controller"]);
        
        if($controller =& ClassRegistry::load($controllerName, "Controller")):
            if(!can_call_method($controller, $action) && !App::path("View", "{$controllerFile}/{$action}.{$this->path['extension']}")):
                $this->error("missingAction", array("controller" => $controllerName, "action" => $action));
                return false;
            endif;
        else:
            if(App::path("View", "{$controllerFile}/{$action}.{$this->path['extension']}")):
                $controller =& ClassRegistry::load("AppController", "Controller");
            else:
                $this->error("missingController", array("controller" => $controllerName));
                return false;
            endif;
        endif;

        $controller->params = $this->path;
        $controller->componentEvent("initialize");
        $controller->beforeFilter();
        $controller->componentEvent("startup");
        if(can_call_method($controller, $action)):
            $params = $this->path["params"];
            if(!is_null($this->path["id"])) $params = array_merge(array($this->path["id"]), $params);
            call_user_func_array(array(&$controller, $action), $params);
        endif;
        if($controller->autoRender):
            $controller->render();
        endif;
        $controller->componentEvent("shutdown");
        echo $controller->output;
        $controller->afterFilter();
        return $controller;
    }
}

?>