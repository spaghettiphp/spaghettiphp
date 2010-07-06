<?php

function array_unset(&$array, $index) {
    if(array_key_exists($index, $array)):
        $item = $array[$index];
        unset($array[$index]);
        return $item;
    endif;
    return null;
}

function array_range($min, $max) {
    $result = array();
    for($i = $min; $i < $max + 1; $i++):
        $result[$i] = $i;
    endfor;
    return $result;
}

function is_hash($var) {
    if(is_array($var)):
        return array_keys($var) !== range(0, sizeof($var) - 1);
    endif;
    return false;
}