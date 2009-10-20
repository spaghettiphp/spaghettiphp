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
     *  Chama o controller e a action solicitadas pela URL.
     * 
     *  @return mixed Instância do novo controller ou falso em caso de erro
     */ 
    public function dispatch() {
        $path = Mapper::parse();
        $path["controller"] = Inflector::hyphenToUnderscore($path["controller"]);
        $path["action"] = Inflector::hyphenToUnderscore($path["action"]);
        $controller_name = Inflector::camelize($path["controller"]) . "Controller";
        $controller_file = $path["controller"];
        $action = $path["action"];
        if($controller =& ClassRegistry::load($controller_name, "Controller")):
            if(!can_call_method($controller, $action) && !App::path("View", "{$controller_file}/{$action}.{$path['extension']}")):
                $this->error("missingAction", array(
                    "controller" => $path["controller"],
                    "action" => $path["action"]
                ));
                return false;
            endif;
        else:
            if(App::path("View", "{$controller_file}/{$action}.{$path['extension']}")):
                $controller =& ClassRegistry::load("AppController", "Controller");
            else:
                $this->error("missingController", array(
                    "controller" => $path["controller"]
                ));
                return false;
            endif;
        endif;
        $controller->params = $path;
        $controller->componentEvent("initialize");
        $controller->beforeFilter();
        $controller->componentEvent("startup");
        if(in_array($action, $controller->methods) && can_call_method($controller, $action)):
            $params = $path["params"];
            if(!is_null($path["id"])) $params = array_merge(array($path["id"]), $params);
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