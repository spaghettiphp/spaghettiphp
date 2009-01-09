<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Folder
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

class Folder extends FileSystem {
    public $path;
    private $_root = ROOT;
    public function __construct($path = null, $mkdir = false) {
        $this->path = $this->_root;
        if(!is_null($path)):
            $this->cd($path);
        endif;
        if($mkdir === true):
            $this->mkdir();
        endif;
    }
    private function cd($path = null) {
        return $this->path = $path;
    }
    public function isDir() {
        if(is_dir($this->path)):
            return true;
        endif;
        return false;
    }
    
    public function ls($filter = true, $sort = "asc") {
        $path = $this->path;
        $sort = ($sort=="asc") ? 0 : 1;
        if($filter):
            $files = array();
            foreach(scandir($path, $sort) as $file):
            if(!preg_match("/^\./", $file)):
                $files[] = $file;
            endif;
            endforeach;
        else:
            $files = scandir($path, $sort);
        endif;
        return $files;
    }
    public function rm() {
        if(!@rmdir($this->path)):
            throw new FolderException("rm() - couldnt remove");
            return false;
        endif;
        return true;
    }

    public function rmdir() {
        return $this->rm();
    }
    public function mkdir($recursive = true) {
        if(@mkdir($this->path, $recursive)):
            return true;
        else:
            throw new FolderException("mkdir()");
            return false;
        endif;
    }
    public static function stripSlashes($path) {
        return trim($path, "/");
    }
    public function __toString() {
        return $this->path;
    }
}

class FolderException extends Exception {}

?>