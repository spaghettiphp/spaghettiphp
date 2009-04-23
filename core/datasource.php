<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

abstract class Datasource extends Object {
    public function __construct($config = array()) {
        $this->config = $config;
    }
}

?>