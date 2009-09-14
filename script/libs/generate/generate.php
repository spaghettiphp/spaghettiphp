<?php
class Generate {
    public static function initFromArguments($arguments) {
        self::dispatch($arguments);
    }
    public static function dispatch($arguments){
        if($arguments["command"] == "") echo "can't call empty command" . PHP_EOL . die();
        
        $fileName = $arguments["params"][0];
        $className = Inflector::camelize($fileName);
        /*
        if($command =& ClassRegistry::load($className, "Script")):
            if(can_call_method($command, "execute")):
                call_user_func_array(array($command, "execute"), $arguments["params"]);
            else:
                echo "can't execute command {$className}" . PHP_EOL;
            endif;
        else:
            echo "command {$className} not found" . PHP_EOL;
        endif;
        */
    }
}
?>