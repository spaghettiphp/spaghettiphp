<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

abstract class Component extends Object {
    public function initialize(&$controller) {
        return true;
    }
    public function startup(&$controller) {
        return true;
    }
    public function shutdown(&$controller) {
        return true;
    }
}

?>