#! /usr/bin/php
<?php
class Folder {
    
    public $path;
    private $_root = ROOT;
    
    public function __construct($path = null){
	$this->path = $this->_root;
	if(!is_null($path)):
	    $this->cd($path);
	endif;
    }
    
    private function cd($path = null){
	return $this->path = $path;
	/*
	$realPath = $this->realPath($path);
	if($realPath):
	    if(!$this->underRoot($realPath)):
		return $this->path = $realPath;
	    else:
		throw new FolderException("can't go under root directory.");
	    endif;
	else:
	    throw new FolderException("'{$path}' doesn't exist.");
	endif;
	*/
    }
    
    public function is_dir() {
	if(is_dir($this->path)):
	    return true;
	endif;
	return false;
    }
    
    public function ls($filter = true, $sort = "asc"){
	$path = $this->path;
	$sort = ($sort=="asc")? 0 : 1;
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
	endif;
    }
    
    public function mkdir() {
	//$path = $this->getPath($name);
	if(@mkdir($this->path)):
	    return true;
	else:
	    throw new FolderException("mkdir()");
	endif;
    }
    
    public function __destruct() {
    }
    
    /**
     * Retorna o diretorio atual, ou o diretorio informado como parametro
     * mas corrigido, ou retorna falso caso o diretorio informado nao exista.
     *
     * @return mixed
     */
    /*
    private function getPath($path = null){
	if(is_null($path)):
	    $path = $this->path;
	else:
	    $path = $this->realPath($path);
	endif;
	if($path)
	    return $path;
	return false;
    }
    
    private function realPath($path = null){
	if(preg_match("/^\/+/", $path)):
	    $path = $this->_root . DS . self::stripSlashes($path);
	else:
	    $path = $this->path . DS . self::stripSlashes($path);
	endif;
	if(realpath($path)):
	    return $path;
	endif;
	return false;
    }
    
    // TO DO
    private function underRoot($path = null){
	// if can't find, it's under root directory
	if(strpos($this->_root . DS,$path) === false):
	    return false;
	endif;
	return false;
    }
    */
    public static function stripSlashes($path) {
	return trim($path, "/");
    }
    
    public function __toString(){
	return $this->path;
    }
    
}

class FolderException extends Exception {}