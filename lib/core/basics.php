<?php
/**
 *  Funcionalidades básicas do Spaghetti.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */
 

/**
 *  App cuida de tarefas relativas a importação de arquivos dentro de uma aplicação
 *  do Spaghetti.
 */
class App extends Object {
    /**
     *  Importa um ou mais arquivos em uma aplicação.
     *
     *  @param string $type Tipo do arquivo a ser importado
     *  @param mixed $file String com o nome de um arquivo ou array com vários arquivos
     *  @param string $ext Extensão do(s) arquivo(s) a ser(em) importado(s)
     *  @return mixed Arquivo incluído ou falso em caso de erro
     */
    public static function import($type, $file, $ext = 'php') {
        if(is_array($file)):
            foreach($file as $file):
                $include = self::import($type, $file, $ext);
            endforeach;
            return $include;
        else:
            if($file_path = self::path($type, $file, $ext)):
                return require_once $file_path;
            else:
                trigger_error("File {$file}.{$ext} doesn't exists in {$type}", E_USER_WARNING);
            endif;
        endif;
        return false;
    }
    /**
     *  Retorna o caminho completo de um arquivo dentro da aplicação.
     *
     *  @param string $type Tipo do arquivo a ser buscado
     *  @param string $file Nome do arquivo a ser buscado
     *  @param string $ext Extensão do arquivo a ser buscado
     *  @return mixed Caminho completo do arquivo ou falso caso não exista
     */
    public static function path($type, $file, $ext = 'php') {
        $paths = array(
            'Config' => SPAGHETTI_ROOT . '/config',
            'Core' => SPAGHETTI_ROOT . '/lib/core',
            'Controller' => APP . '/controllers',
            'Model' => APP . '/models',
            'View' => APP . '/views',
            'Layout' => APP . '/layouts',
            'Component' => APP . '/components',
            'Helper' => APP . '/helpers',
            'App' => APP,
            'Datasource' => SPAGHETTI_ROOT . '/lib/core/model/datasources'
        );
 
        $path = $paths[$type];
        $file_path = $path . '/' . $file . '.' . $ext;
        if(file_exists($file_path)):
            return $file_path;
        endif;
        return false;
    }
}

/**
 *  Error é a classe que trata os erros do Spaghetti, renderizando telas de erro
 *  amigáveis.
 */
class Error extends Object {
    public function __construct($type = '', $details = array()) {
        $view = new View;
        $filename = Inflector::underscore($type);
        $viewFile = App::path('View', 'errors/' . $filename . '.htm');
        if(!$viewFile):
            $viewFile = App::path('View', 'errors/missing_error.htm');
            $details = array('error' => $type);
        endif;
        $content = $view->renderView($viewFile, array('details' => $details));
        echo $view->renderLayout($content, 'error', 'htm');
        $this->stop();
    }
}