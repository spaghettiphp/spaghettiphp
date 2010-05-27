<?php

abstract class Component {
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