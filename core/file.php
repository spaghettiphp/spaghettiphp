<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.File
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

abstract class FileSystem extends Object {
}

class File extends FileSystem {
    private $file;
    private $path;
    private $filename;
    private $mode;
    public function __construct($name = null, $mode = "r") {
        $this->path = dirname($name);
        $this->filename = basename($name);
        $this->mode = $mode;
        $this->file = fopen($this->getPath(), $this->mode);
    }
    public function __destruct() {
        return fclose($this->file);
    }
    public function write($content = "") {
        return fwrite($this->file, $content);
    }
    public function read($length = null) {
        if(is_null($length)):
            $length = $this->size();
        endif;
        return fread($this->file, $length);
    }
    public function size() {
        return filesize($this->getPath());
    }
    public function rewind() {
        return rewind($this->file);
    }
    public function gets($length = null) {
        return fgets($this->file, $length);
    }
    public function eof() {
        return feof($this->file);
    }
    public function rename($name = null) {
        fclose($this->file);
        if(rename($this->getPath(), $name)):
            $this->file = fopen($name, $this->mode);
            return true;
        endif;
        return false;
    }
    public function getPath() {
        return self::path("{$this->path}/{$this->filename}");
    }
    static public function exists($path = null) {
        return file_exists(self::path($path));
    }
    static public function path($filename = "") {
        return ROOT . DS . $filename;
    }
}

?>