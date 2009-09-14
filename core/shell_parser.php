<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */
class ShellParser {
    public function parse($rawArguments = array()){
        if(count($rawArguments) > 2)
            $params = array_slice($rawArguments, 2);
        return array(
            "script" => end(explode("/", $rawArguments[0])),
            "command" => $rawArguments[1],
            "params" => $params
        );
    }
}
?>