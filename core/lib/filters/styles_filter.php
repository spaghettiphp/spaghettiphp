<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Lib.Filter.Styles
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class StylesFilter extends Filter {
    function start($file = "") {
        $this->file = $this->parse_filename($file);
        if($file = Spaghetti::import("Webroot", "styles/{$this->file['filename']}", $this->file["extension"], true)):
            ob_start();
            header("Content-Type: text/css");
            include $file;
            echo ob_get_clean();
        else:
            header("HTTP/1.1 404 Not Found");
        endif;
    }
}

?>