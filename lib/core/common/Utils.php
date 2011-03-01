<?php

function array_unset(&$array, $index) {
    if(array_key_exists($index, $array)) {
        $item = $array[$index];
        unset($array[$index]);
        return $item;
    }
}

function is_hash($var) {
    if(is_array($var)) {
        return array_keys($var) !== range(0, sizeof($var) - 1);
    }
    else {
        return false;
    }
}