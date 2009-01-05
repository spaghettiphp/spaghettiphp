<?php
class Shell extends Object {
    public function error($text = null){
        $this->log($text, "error");
        die();
    }
    public function log($message = null, $type = "created"){
        $type = sprintf("%15s ", $type);
        echo "{$type}{$message}\n";
    }
}

class ShellDispatcher extends Shell {
    public $rawArguments = array();
    public $arguments = array();
    public $params = array();
    
    public function __construct(){
        $this->rawArguments = $_SERVER["argv"];
        $this->parseArguments();
        $this->dispatch();
    }
    
    private function parseArguments(){
        if(count($this->rawArguments) > 2):
            $this->params = array_slice($this->rawArguments, 2, count($this->rawArguments));
        endif;
        $this->arguments = array(
            "script" => $this->rawArguments[0],
            "command" => $this->rawArguments[1],
            "params" => $this->params
        );
        return $this->arguments;
    }
    
    private function dispatch(){        
        if($this->arguments["command"]=="")
            $this->error("commandEmpty");
        
        $fileName = Inflector::underscore($this->arguments["command"])."_command";
        $className = Inflector::camelize($fileName);
        
        if(App::import("Command", $fileName, "php", true)):
            $command =& ClassRegistry::init($className, "Command");
            call_user_func_array(array($command,"execute"), $this->arguments["params"]);
        else:
            $this->error("command \"{$className}\" not found");
        endif;
    }
}
?>