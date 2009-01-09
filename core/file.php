<?php
class FileSystem extends Object {}

class File extends FileSystem {
    public $path = "";
    public $filename = "";
    public $content = "";
    public $writeType = "w";
    public function __construct($path, $filename, $content, $writeType = null){
        $this->path = trim($path, "/")."/";
        $this->filename = $filename;
        $this->content = $content;
        $this->writeType = (is_null($writeType)) ? $this->writeType : $writeType;
    }
    public function exists(){
        if(file_exists($this->path.$this->filename))
            return true;
        return false;
    }
    public function save(){
        $fileHandler = fopen($this->path.$this->filename, $this->writeType);
        if($fileHandler):
            fwrite($fileHandler, $this->content);
            fclose($fileHandler);
        else:
            return false;
        endif;
    }
}
/*
class Folder extends FileSystem {
    public $path = "";
    public $chmod = 0755;
    public function __construct($path, $chmod = null){
        $this->path = trim($path, "/")."/";
        $this->chmod = (is_null($chmod)) ? $this->chmod : $chmod;
    }
    public function exists(){
        if(is_dir($this->path))
            return true;
        return false;
    }
    public function save(){
        if(@mkdir($this->path, $this->chmod, true))
            return true;
        return false;
    }
}
*/
?>