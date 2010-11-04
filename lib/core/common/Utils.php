<?php

function array_unset(&$array, $index) {
    if(array_key_exists($index, $array)) {
        $item = $array[$index];
        unset($array[$index]);
        return $item;
    }
}

function array_range($min, $max) {
    $result = array();

    for($i = $min; $i < $max + 1; $i++) {
        $result[$i] = $i;
    }
    
    return $result;
}

function is_hash($var) {
    if(is_array($var)) {
        return array_keys($var) !== range(0, sizeof($var) - 1);
    }
    else {
        return false;
    }
}

if(!function_exists('get_called_class')) {
    function get_called_class($bt = false, $l = 1) {
        if(!$bt) {
            $bt = debug_backtrace();
        }
        if(!array_key_exists($l, $bt)) {
            throw new Exception('Cannot find called class -> stack level too deep.');
        }
        if(!array_key_exists('type', $bt[$l])) {
            throw new Exception('type not set');
        }
        if($bt[$l]['type'] == '::') {
            $lines = file($bt[$l]['file']);
            $i = 0;
            $callerLine = '';
            do {
                $i++;
                $callerLine = $lines[$bt[$l]['line'] - $i] . $callerLine;
            } while(stripos($callerLine, $bt[$l]['function']) === false);
            
            preg_match(
                '/([a-zA-Z0-9\_]+)::' . $bt[$l]['function'] . '/',
                $callerLine,
                $matches
            );
            
            if(!isset($matches[1])) {
                throw new Exception('Could not find caller class: originating method call is obscured.');
            }
            switch($matches[1]) {
                case 'self':
                case 'parent':
                    return get_called_class($bt, $l + 1);
                default:
                    return $matches[1];
            }
        }
        else {
            throw new Exception('Unknown backtrace method type');
        }
    }
}