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
     *  Define se o layout será renderizado automaticamente.
     */
    public $autoLayout = true;
    /**
     *  Define se a view será renderizada automaticamente.
     */
    public $autoRender = true;
    /**
     *  Componentes a serem carregados no controller.
     */
    public $components = array();
    /**
     *  Helpers a serem carregados pela view.
     */
    public $helpers = array("Html", "Form", "Pagination");
    /**
     *  Valores enviados através de $_POST.
     */
    public $data = array();
    /**
     *  Layout a ser renderizado.
     */
    public $layout = "default";
    /**
     *  Nome do controller.
     */
    public $name = null;
    /**
     *  Conteúdo de saída gerado pela view.
     */
    public $output = "";
    /**
     *  Parâmetros interpretados por Mapper.
     */
    public $params = array();
    /**
     *  Modelos utilizados pelo controller.
     */
    public $uses = null;
    /**
     *  Variáveis a serem enviadas para a view.
     */
    public $viewData = array();
    public $methods = array();

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
        
        $this->methods = $this->getMethods();
        $this->data = $_POST;
        $this->loadComponents();
        $this->loadModels();
    }
    /**
     *  Retorna os métodos do controller.
     *
     *  @return array Método utilizado
     */
    public function getMethods() {
        $child = get_class_methods($this);
        $parent = get_class_methods("Controller");
        return array_diff($child, $parent);
    }
    /**
     *  Carrega todos os componentes associados ao controller.
     *
     *  @return boolean Verdadeiro se todos os componentes foram carregados
     */
    public function loadComponents() {
        foreach($this->components as $component):
            $component = "{$component}Component";
            if(!$this->{$component} = ClassRegistry::load($component, "Component")):
                $this->error("missingComponent", array("component" => $component));
                return false;
            endif;
        endforeach;
        return true;
    }
    /**
     *  Executa um evento em todos os componentes do controller.
     *
     *  @param string $event Evento a ser executado
     *  @return void
     */
    public function componentEvent($event = null) {
        foreach($this->components as $component):
            $className = "{$component}Component";
            if(can_call_method($this->$className, $event)):
                $this->$className->{$event}($this);
            else:
                trigger_error("Can't call method {$event} in {$className}", E_USER_WARNING);
            endif;
        endforeach;
    }
    /**
     *  Carrega todos os models associados ao controller.
     *
     *  @return boolean Verdadeiro caso todos os models foram carregados
     */
    public function loadModels() {
        foreach($this->uses as $model):
            if(!$this->{$model} = ClassRegistry::load($model)):
                $this->error("missingModel", array("model" => $model));
                return false;
            endif;
        endforeach;
        return true;
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
     *
     *  @return true
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
     *  Renderiza uma view.
     *
     *  @param string $action Nome da action a ser renderizada
     *  @param string $layout Nome do layout a ser renderizado
     *  @return string Resultado da renderização
     */
    public function render($action = null, $layout = null) {
        $this->beforeRender();
        $view = new View($this);
        $this->autoRender = false;
        return $this->output .= $view->render($action, $layout);
    }
    /**
     *  Limpa o conteúdo de saída do controller.
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
     *  @param boolean $exit Verdadeiro para encerrar o script após o redirecionamento
     *  @return void
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
     *  @param string $var Nome da variável a ser definida
     *  @param mixed $value Valor da variável
     *  @return mixed Valor da variável
     */
    public function set($var, $value = null) {
        if(is_array($var)):
            foreach($var as $key => $value):
                $this->set($key, $value);
            endforeach;
        elseif(!is_null($value)):
            return $this->viewData[$var] = $value;
        endif;
        return true;
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
     *  Retorna o valor de um parâmetro da URL.
     *
     *  @param string $key Chave do valor a ser retornado
     *  @return string Valor do parâmetro
     */
    public function param($key = null) {
        if(isset($this->params["named"][$key])):
            return $this->params["named"][$key];
        elseif(in_array($key, array_keys($this->params))):
            return $this->params[$key];
        endif;
        return null;
    }
    /**
     *  Retorna a o número de página atual.
     *
     *  @param string $param Parâmetro contento o número de página
     *  @return integer Número de página atual
     */
    public function page($param = "page") {
        $page = $this->param($param);
        if(is_null($page) || empty($page)):
            $page = 1;
        endif;
        return $page;
    }
}

?>