<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

function pr($data) {
    echo "<pre>" . print_r($data, true) . "</pre>";
}

function dump($data) {
    pr(var_export($data, true));
}

function if_string($condition, $string) {
    if($condition && $condition != ""):
        return $string;
    endif;
    return "";
}

function pick() {
    $args = func_get_args();
    foreach($args as $arg):
        if($arg !== null):
            return $arg;
        endif;
    endforeach;
    return null;
}

function array_unset($array = array(), $index = "") {
    $item = $array[$index];
    unset($array[$index]);
    return $item;
}

function can_call_method(&$object, $method) {
    if(method_exists($object, $method)):
        $method = new ReflectionMethod($object, $method);
        return $method->isPublic();
    endif;
    return false;
}

?>