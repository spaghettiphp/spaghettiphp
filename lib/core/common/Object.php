<?php

abstract class Object {
    protected function error($type, $details = array()) {
        new Error($type, $details);
    }
    protected function stop($status = null) {
        exit($status);
    }
}