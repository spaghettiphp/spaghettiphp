<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Lib.Filter.Images
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class ImagesFilter extends Filter {
    function start($file = "") {
        $this->file = $this->parse_filename($file);
        if($file = Spaghetti::import("Webroot", "images/{$this->file['filename']}", $this->file["extension"], true)):
            header("Location: " . Mapper::url($file, true));
        else:
            header("HTTP/1.1 404 Not Found");
        endif;
    }
}

?>