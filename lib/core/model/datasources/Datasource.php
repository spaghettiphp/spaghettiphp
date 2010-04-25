<?php

abstract class Datasource extends Object {
    public function __construct($config) {
        $this->config = $config;
    }
}