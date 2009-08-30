<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Misc
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
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

function array_unset(&$array = array(), $index = "") {
    $item = $array[$index];
    unset($array[$index]);
    return $item;
}

?>