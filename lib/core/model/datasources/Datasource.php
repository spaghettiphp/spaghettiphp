<?php

abstract class Datasource {
    public function __construct($config) {
        $this->config = $config;
    }
}