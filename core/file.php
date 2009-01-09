<?php

abstract class FileSystem extends Object {
}

class File extends FileSystem {
    private $file;
    private $path;
    private $filename;
    private $mode;
    public function __construct($name = null, $mode = "r") {
        $this->file = fopen($name, $mode);
        $this->path = dirname($name);
        $this->filename = basename($name);
        $this->mode = $mode;
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
        return "{$this->path}/{$this->filename}";
    }
    static public function exists($path = null) {
        return file_exists($path);
    }
    static public function get($file = null) {
        return file_get_contents($file);
    }
    static public function put($file = null, $data = null) {
        return file_put_contents($file, $data);
    }
}

?>