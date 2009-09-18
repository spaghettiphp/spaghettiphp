<?php
/**
 *  View é a classe responsável por gerar a saída dos controllers e renderizar a
 *  view e layout correspondente, além do carregamento dos helpers necessários.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class View extends Object {
    /**
     *  Define se o layout será renderizado automaticamente.
     */
    public $autoLayout;
    /**
     *  Dados enviados pelo controller.
     */
    public $data = array();
    /**
     *  Helpers a serem carregados.
     */
    public $helpers = array("Html");
    /**
     *  Helpers já carregados.
     */
    public $loadedHelpers = array();
    /**
     *  Layout a ser utilizado na renderização.
     */
    public $layout;
    /**
     *  Título da página.
     */
    public $pageTitle;
    /**
     *  Parâmetros definidos no controller.
     */
    public $params = array();
    
    public function __construct(&$controller = null) {
        if($controller):
            $this->autoLayout = $controller->autoLayout;
            $this->helpers = $controller->helpers;
            $this->params = $controller->params;
            $this->layout = $controller->layout;
            $this->data = $controller->viewData;
        endif;
        $this->loadHelpers();
    }
    /**
     *  Carrega os helpers definidos.
     *
     *  @return array Instâncias dos helpers
     */
    public function loadHelpers() {
        foreach($this->helpers as $helper):
            $class = "{$helper}Helper";
            $helper = Inflector::underscore($helper);
            if(App::path("Helper", Inflector::underscore($class))):
                App::import("Helper", Inflector::underscore($class));
                $this->loadedHelpers[$helper] = new $class;
            else:
                $this->error("missingHelper", array("helper" => $class));
                return false;
            endif;
        endforeach;
        return $this->loadedHelpers;
    }
    /**
     *  Renderiza um arquivo de view.
     *
     *  @param string $filename Nome do arquivo a renderizar
     *  @param array $data Dados a serem extraídos durante a renderização
     *  @return string Resultado da renderização
     */
    public function renderView($filename, $data = array()) {
        extract($data, EXTR_OVERWRITE);
        extract($this->loadedHelpers, EXTR_PREFIX_SAME, "helper");
        ob_start();
        include $filename;
        $output = ob_get_clean();
        return $output;
    }
    /**
     *  Renderiza uma view.
     *
     *  @param string $action Action a ser renderizada
     *  @param string $layout Layout a ser renderizado
     *  @return string Resultado da renderização
     */
    public function render($action = null, $layout = null) {
        if(is_null($action)):
            $controller = $this->params["controller"];
            $action = $this->params["action"];
            $ext = $this->params["extension"];
            $layout = $this->layout;
        else:
            $filename = explode(".", $action);
            $controller = null;
            $action = $filename[0];
            $ext = $filename[1] ? $filename[1] : $this->params["extension"];
        endif;
        $file = App::path("View", "{$controller}/{$action}.{$ext}");
        if($file):
            $output = $this->renderView($file, $this->data);
            if($this->autoLayout && $layout):
                $output = $this->renderLayout($output, $layout, $ext);
            endif;
            return $output;
        else:
            $this->error("missingView", array(
                "controller" => $controller,
                "view" => $action,
                "extension" => $ext)
            );
            return false;
        endif;
    }
    /**
     *  Renderiza um layout.
     *
     *  @param string $content Conteúdo a ser injetado no layout
     *  @param string $layout Layout a ser renderizado
     *  @param string $ext Extensão de arquivo do layout
     *  @return string Resultado da renderização
     */
    public function renderLayout($content, $layout, $ext = null) {
        if(is_null($ext)):
            $ext = $this->params["extension"];
        endif;
        $file = App::path("Layout", "{$layout}.{$ext}");
        if($file):
            $this->contentForLayout = $content;
            return $this->renderView($file, $this->data);
        else:
            $this->error("missingLayout", array(
                "layout" => $layout,
                "extension" => $ext
            ));
            return false;
        endif;        
    }
    /**
     *  Renderiza um elemento.
     *
     *  @param string $element Elemento a ser renderizado
     *  @param array $params Dados as serem extraídos na renderização
     *  @return string Resultado da renderização
     */
    public function element($element, $params = array()) {
        $element = dirname($element) . DS . "_" . basename($element);
        $ext = $this->params["extension"] ? $this->params["extension"] : Config::read("defaultExtension");
        return $this->renderView(App::path("View", "{$element}.{$ext}"), $params);
    }
}

?>