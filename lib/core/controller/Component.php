<?php

abstract class Component {
    public function initialize($controller) { }
    public function startup($controller) { }
    public function shutdown($controller) { }
}