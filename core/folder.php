<?php
/**
 *  Destinado ao gerenciamento de diretórios, leitura, escrita,
 *  dentre outras funcionalidades.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Folder extends FileSystem {
    /**
     *  Localização do arquivo.
     * 
     *  @var string
     */
    public $path;
    /**
     *  Localização do framework.
     * 
     *  @var string
     */
    private $_root = ROOT;
    /**
     *  Construtor.
     * 
     *  @param string $path Localização
     *  @param boolean $mkdir Cria o diretório (se verdadeiro) caso não exista
     */
    public function __construct($path = null, $mkdir = false) {
        $this->path = $this->_root;
        if(!is_null($path)):
            $this->cd($path);
        endif;
        if($mkdir === true):
            $this->mkdir();
        endif;
    }
    /**
     *  Muda a localização.
     * 
     *  @param string $path Localiação
     *  @return string Nova Localização
     */
    private function cd($path = null) {
        return $this->path = $path;
    }
    /**
     *  Verifica se a localização (path) é um diretório.
     * 
     *  @return boolean Verdadeiro caso seja um diretório
     */
    public function isDir() {
        if(is_dir($this->path)):
            return true;
        endif;
        return false;
    }
    /**
     *  Lista o conteúdo do diretório.
     * 
     *  @param boolean $filter Aplica ou não o filtro
     *  @param string $sort Ordena em ordem ascendente ou descendente
     *  @return string Conteúdo do diretório
     */
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
    /**
     *  Remove o diretório em questão.
     * 
     *  @return boolean Verdadeiro para removido com sucesso
     */
    public function rm() {
        if(!@rmdir($this->path)):
            throw new FolderException("rm() - couldnt remove");
            return false;
        endif;
        return true;
    }
    /**
     *  Remove o diretório em questão.
     *
     *  @return boolean Verdadeiro para removido com sucesso
     */
    public function rmdir() {
        return $this->rm();
    }
    /**
     *  Cria diretório de acordo com o path.
     * 
     *  @param boolean $recursive Cria subpastas (se verdadeiro)
     *  @return boolean Verdadeiro para criado com sucesso
     */
    public function mkdir($recursive = true) {
        if(@mkdir($this->path, $recursive)):
            return true;
        else:
            throw new FolderException("mkdir()");
            return false;
        endif;
    }
    /**
     *  Retirando a barra final do path.
     * 
     *  @param string $path Localização do diretório
     *  @return string Localização sem a barra "/"
     */
    public static function stripSlashes($path) {
        return trim($path, "/");
    }
    /**
     *  Retornando a localização o diretório
     * 
     *  @return string Localização
     */
    public function __toString() {
        return $this->path;
    }
}

class FolderException extends Exception {}

?>