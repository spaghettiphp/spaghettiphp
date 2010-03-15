<?php

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