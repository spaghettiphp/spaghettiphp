<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Lib.Filter.Scripts
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class ScriptsFilter extends Filter {
    function start($file = "") {
        $this->file = $this->parse_filename($file);
        if($file = Spaghetti::import("Webroot", "scripts/{$this->file['filename']}", $this->file["extension"], true)):
            ob_start();
            header("Content-Type: text/javascript");
            include $file;
            echo ob_get_clean();
        else:
            header("HTTP/1.1 404 Not Found");
        endif;
    }
}

?>