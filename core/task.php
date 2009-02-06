<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Task extends Shell {
    public function __construct() {
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