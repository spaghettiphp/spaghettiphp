<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Inflector
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Inflector extends Object {
    public function camelize($string = "") {
        return str_replace(" ", "", ucwords(str_replace(array("_", "-"), " ", $string)));
    }
    public function humanize($string = "") {
        return ucwords(str_replace(array("_", "-"), " ", $string));
    }
    public function underscore($string = "") {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
    }
    public function slug($string = "", $replace = "-") {
        $map = array(
            "/à|á|å|â/" => "a",
            "/è|é|ê|ẽ|ë/" => "e",
            "/ì|í|î/" => "i",
            "/ò|ó|ô|ø/" => "o",
            "/ù|ú|ů|û/" => "u",
            "/ç/" => "c",
            "/ñ/" => "n",
            "/ä|æ/" => "ae",
            "/ö/" => "oe",
            "/ü/" => "ue",
            "/Ä/" => "Ae",
            "/Ü/" => "Ue",
            "/Ö/" => "Oe",
            "/ß/" => "ss",
            "/[^\w\s]/" => " ",
            "/\\s+/" => $replace,
            "/^{$replace}+|{$replace}+$/" => ""
        );
        return strtolower(preg_replace(array_keys($map), array_values($map), $string));
    }
}

?>