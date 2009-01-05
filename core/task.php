<?php
class Task extends Shell {
    public $uses = array();
    public function __construct() {
        $this->loadModels();
    }
    public function execute() {}
    final public function out($content = null) {
        if(is_array($content)):
            print_r($content);
            echo PHP_EOL;
        else:
            echo $content.PHP_EOL;
        endif;
    }
    private function loadModels() {
        foreach($this->uses as $model):
            $this->{$model} =& ClassRegistry::init($model);
        endforeach;
    }
}
?>