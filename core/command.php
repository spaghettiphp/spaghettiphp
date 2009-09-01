<?php
/**
 *  Utilitário para geração automática do código.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Command extends Shell {
    /**
     *  Tarefas a serem executadas.
     *
     *  @var array
     */
    public $tasks = array();
    /**
     *  Carregando as tarefas.
     */
    public function __construct() {
        $this->loadTasks();
    }
    /**
     *  Carrega as tarefas (classes) a serem executadas, registrando-as.
     *
     *  @return void
     */
    private function loadTasks() {
        foreach($this->tasks as $task):
            $className = "{$task}Task";
            $this->{$task} =& ClassRegistry::init($className, "Task");
        endforeach;
    }
    /**
     *  Executa um comando em questão.
     */
    public function execute() {}
    /**
     *  Exibe o conteudo do comando executado.
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