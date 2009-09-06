<?php
/**
 *  Utilização no console para gerenciamento das tarefas.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Task extends Shell {
    /**
     *  Construtor.
     */
    public function __construct() {
    }
    /**
     *  Executa uma tarefa em questão.
     */
    public function execute() {}
    /**
     *  Exibe o conteudo da tarefa executada.
     *
     *  @param mixed $content Conteudo a ser exibido
     */
    public function out($content = null) {
        if(is_array($content)):
            print_r($content);
            echo PHP_EOL;
        else:
            echo $content . PHP_EOL;
        endif;
    }
}

?>