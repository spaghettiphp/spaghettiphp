<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Command
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

class Command extends Shell {
    public $tasks = array();
    public function __construct() {
        $this->loadTasks();
    }
    private function loadTasks() {
        foreach($this->tasks as $task):
            $className = "{$task}Task";
            $this->{$task} =& ClassRegistry::init($className, "Task");
        endforeach;
    }
    public function execute() {}
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