<?php
/**
 *  A classe Controller é responsável pela camada Controller da aplicação.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Controller
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
    
class Controller extends Object {
    /**
     * Renderizar layout automaticamente
    */
    public $auto_layout = true;
    /**
     * Renderizar view automaticamente
    */
    public $auto_render = true;
    /**
     * Componentes carregados no controller
    */
    public $components = array();
    /**
     * $_POST
     */
    public $data = array();
    /**
     * Helpers carregados nas views do controller
     */
    public $helpers = array("Html");
    /**
     * Layout a ser renderizado
     */
    public $layout = "default";
    /**
     * Nome do controller
     */
    public $name = null;
    /**
     * Conteúdo de saída da view
     */
    public $output = "";
    /**
     * Título da página HTML
     */
    public $page_title = "";
    /**
     * Parâmetros da URL
     */
    public $params = array();
    /**
     * Modelos utilizados pelo controller
     */
    public $uses = null;
    /**
     * Variáveis passadas para a view
     */
    public $view_data = array();
    
    public function __construct() {
        /**
         * Define o nome do controller com base no nome da classe
         */
        if($this->name === null && preg_match("/(.*)Controller/", get_class($this), $name)):
            if($name[1] && $name[1] != "App"):
                $this->name = $name[1];
            elseif($this->uses === null):
                $this->uses = array();
            endif;
        endif;
        if($this->uses === null):
            $this->uses = array($this->name);
        endif;
        
        /**
         * Define Controller::data com o conteúdo da variável global $_POST
         */
        $this->data = $_POST;
        $this->Component =& new Component;
        $this->Component->init($this);
        /**
         * Carrega os modelos
         */ 
        $this->load_models();
    }
    /**
     * Este método carrega os models associados através da propriedade
     * Controller::uses, registrando as classes no registro de classes.
     *
     * @return void
     */
    public function load_models() {
        foreach($this->uses as $model):
            $this->{$model} =& ClassRegistry::init($model);
        endforeach;
    }
    /**
     * Método a ser chamado antes de um filtro ser executado
     */
    public function before_filter() {
        return true;
    }
    /**
     * Método a ser chamado antes da renderização da view
     *
     * @return bolean
     */
    public function before_render() {
        return true;
    }
    /**
     * Método a ser chamado após a execução de um filtro
     */
    public function after_filter() {
        return true;
    }
    /**
     * O método Controller::set_action() define a ação solicitada pela URL,
     * chamando o método do controller com o mesmo nome e passando os parâmetros
     * da URL como parâmetros da função.
     *
     * @param string $action Nome da ação
     * @return void
     */
    public function set_action($action) {
        $this->params["action"] = $action;
        $args = func_get_args();
        unset($args[0]);
        call_user_func_array(array(&$this, $action), $args);
    }
    /**
     * Renderiza a view da action atual, utilizando o layout informado. Cria uma
     * nova instância da classe View.
     *
     * @param string $action Nome da action a ser chamada
     * @param string $layout Nome do layout
     * @return string Conteúdo da saída renderizada
     */
    public function render($action = null, $layout = null) {
        $this->before_render();
        $view = new View($this);
        $view->set($this->view_data);
        $view->helpers = $this->helpers;
        $this->output .= $view->render($action, $layout);
        $this->auto_render = false;
        return $this->output;
    }
    /**
     * O método Controller::clear() limpa a saída para a view
     *
     * @return boolean
     */
    public function clear() {
        $this->output = "";
        return true;
    }
    /**
     * Faz um redirecionamento enviando um cabeçalho HTTP com o código de status
     * de acordo com a RFC(...)
     *
     * @param string $url URL para redirecionamento
     * @param integer $status Código do status
     * @param boolean $exit
     * @return
     */
    public function redirect($url = "", $status = null, $exit = true) {
        $this->auto_render = false;
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
        if($status !== null && isset($codes[$status])):
            header("HTTP/1.1 {$status} {$codes[$status]}");
        endif;
        header("Location: " . Mapper::url($url, true));
        if($exit) die();
    }
    /**
     * O método Controller::set() define uma variável para ser
     * passada para a view.
     *
     * @param string $var Nome da variável
     * @param mixed $content Conteúdo da variável
     * @return mixed
     */
    public function set($var = null, $content = null) {
        if(is_array($var)):
            foreach($var as $key => $value):
                $this->set($key, $value);
            endforeach;
            return true;
        elseif($var !== null):
            $this->view_data[$var] = $content;
            return $this->view_data[$var];
        endif;
        return false;
    }
    /**
     * return mixed
     */
    public function params($param = null) {
        return $this->params[$param];
    }
}

?>