<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Filter
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Filter extends Object {
    function start($file = "") {
        $this->file = $this->parse_filename($file);
        if($file = Spaghetti::import("Webroot", "{$this->file['filename']}", $this->file["extension"], true)):
            ob_start();
            include $file;
            echo ob_get_clean();
        else:
            header("HTTP/1.1 404 Not Found");
        endif;
    }
    function parse_filename($content = "") {
        $this->file = array();
        preg_match("/([^.]*).?([^.]+)?/", $content, $reg);
        $parts = array("full", "filename", "extension");
        foreach($parts as $k => $key) {
            $this->file[$key] = isset($reg[$k]) ? $reg[$k] : "";
        }
        return $this->file;
    }
}

?>