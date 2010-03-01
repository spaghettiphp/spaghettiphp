<?php

abstract class Datasource extends Object {
    public function __construct($config = array()) {
        $this->config = $config;
    }
}