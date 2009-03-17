<?php
/**
 *  Controller permite que seja adicionada lógica a uma aplicação, além de prover
 *  funcionalidades básicas, como renderização de views, redirecionamentos, acesso
 *  a modelos de dados, entre outros.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Controller extends Object {
    /**
     *  Renderizar layout automaticamente.
    */
    public $autoLayout = true;
    /**
     *  Renderizar view automaticamente.
    */
    public $autoRender = true;
    /**
     *  Componentes a serem carregados no controller.
    */
    public $components = array();
    /**
     *  Valores enviados através de $_POST.
     */
    public $data = array();
    /**
     *  Helpers a serem carregados por uma view.
     */
    public $helpers = array("Html", "Form");
    /**
     *  Layout a ser renderizado.
     */
    public $layout = "default";
    /**
     *  Nome do controller.
     */
    public $name = null;
    /**
     *  Conteúdo de saída gerado por uma view.
     */
    public $output = "";
    /**
     *  Título da página.
     */
    public $pageTitle = "";
    /**
     *  Parâmetros parseados por Dispatcher.
     */
    public $params = array();
    /**
     *  Modelos utilizados pelo controller.
     */
    public $uses = null;
    /**
     *  Variáveis a serem enviadas para uma view.
     */
    public $viewData = array();
    
    public function __construct() {
        if(is_null($this->name) && preg_match("/(.*)Controller/", get_class($this), $name)):
            if($name[1] && $name[1] != "App"):
                $this->name = $name[1];
            elseif(is_null($this->uses)):
                $this->uses = array();
            endif;
        endif;
        if(is_null($this->uses)):
            $this->uses = array($this->name);
        endif;
        
        $this->data = $_POST;
        $this->loadComponents();
        $this->loadModels();
    }
    public function loadComponents() {
        foreach($this->components as $component):
            $component = "{$component}Component";
            if(!$this->{$component} = ClassRegistry::init($component, "Component")):
                $this->error("missingComponent", array("component" => $component));
            endif;
        endforeach;
        return true;
    }
    public function initialize() {
        foreach($this->components as $component):
            $component->initialize($this);
        endforeach;
        return true;
    }
    public function startup() {
        foreach($this->components as $component):
            $component->startup($this);
        endforeach;
    }
    public function shutdown() {
        foreach($this->components as $component):
            $component->shutdown($this);
        endforeach;
    }
    /**
     *  Carrega todos os models associados ao controller.
     *
     *  @return void
     */
    public function loadModels() {
        foreach($this->uses as $model):
            $this->{$model} =& ClassRegistry::init($model);
        endforeach;
    }
    /**
     *  Callback executado antes de qualquer ação do controller.
     *
     *  @return true
     */
    public function beforeFilter() {
        return true;
    }
    /**
     *  Callback executado antes da renderização de uma view.
     *
     *  @return true
     */
    public function beforeRender() {
        return true;
    }
    /**
     *  Callback executado após as ações do controller.
     */
    public function afterFilter() {
        return true;
    }
    /**
     *  Redireciona uma action para outra.
     *
     *  @param string $action Nome da action a ser redirecionada
     *  @return mixed Retorno da action redirecionada
     */
    public function setAction($action) {
        $this->params["action"] = $action;
        $args = func_get_args();
        unset($args[0]);
        return call_user_func_array(array(&$this, $action), $args);
    }
    /**
     *  Renderiza action atual, utilizando o layout informado.
     *
     *  @param string $action Nome da action a ser renderizada
     *  @param string $layout Nome do layout
     *  @return string Conteúdo gerado pela view
     */
    public function render($action = null, $layout = null) {
        $this->beforeRender();
        $view = new View($this);
        $view->set($this->viewData);
        $view->helpers = $this->helpers;
        $this->output .= $view->render($action, $layout);
        $this->autoRender = false;
        return $this->output;
    }
    /**
     *  Limpa o conteúdo de saída do controller
     *
     *  @return true
     */
    public function clear() {
        $this->output = "";
        return true;
    }
    /**
     *  Faz um redirecionamento enviando um cabeçalho HTTP com o código de status.
     *
     *  @param string $url URL para redirecionamento
     *  @param integer $status Código do status
     *  @param boolean $exit
     *  @return
     */
    public function redirect($url = "", $status = null, $exit = true) {
        $this->autoRender = false;
        $codes = array(
            100 => "Continue",
            101 => "Switching Protocols",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            300 => "Multiple Choices",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            305 => "Use Proxy",
            307 => "Temporary Redirect",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Time-out",
            409 => "Conflict",
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Request Entity Too Large",
            414 => "Request-URI Too Large",
            415 => "Unsupported Media Type",
            416 => "Requested range not satisfiable",
            417 => "Expectation Failed",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Time-out"
        );
        if(!is_null($status) && isset($codes[$status])):
            header("HTTP/1.1 {$status} {$codes[$status]}");
        endif;
        header("Location: " . Mapper::url($url, true));
        if($exit) $this->stop();
    }
    /**
     *  Define uma variável a ser passada para uma view.
     * 
     *  @param string $var Nome da variável
     *  @param mixed $value Valor da variável
     *  @return mixed
     */
    public function set($var = null, $value = null) {
        if(is_array($var)):
            foreach($var as $key => $value):
                $this->set($key, $value);
            endforeach;
            return true;
        elseif(!is_null($var)):
            $this->viewData[$var] = $value;
            return $this->viewData[$var];
        endif;
        return false;
    }
    /**
     *  Recupera uma variável de Controller::viewData.
     *
     *  @param string $var Nome da variável a ser lida
     *  @return mixed Valor da variável
     */
    public function get($var = null) {
        if(!is_null($var)):
            if(isset($this->viewData[$var])):
                return $this->viewData[$var];
            endif;
        endif;
        return false;
    }
    /**
     *  Retorna o valor de um parâmetro da URL
     *
     *  @param string $param Nome do valor a ser retornado
     *  @return string Valor do parâmetro
     */
    public function param($param = null) {
        return $this->params[$param];
    }
}

?>