<?php
/**
 *  Destinado ao gerenciamento de arquivos, leitura, escrita,
 *  dentre outras funcionalidades.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

abstract class FileSystem extends Object {
}

class File extends FileSystem {
    /**
     *  Ponteiro para abertura do arquivo.
     *
     *  @var resource
     */
    private $file;
    /**
     *  Localização do arquivo.
     *
     *  @var string
     */
    private $path;
    /**
     *  Nome do arquivo.
     *
     *  @var string
     */
    private $filename;
    /**
     *  Modo de abertura do arquivo.
     *
     *  @var string
     */
    private $mode;
    /**
     *  Construtor.
     *  Seta o path, nome do arquivo, modo de abertura e ponteiro apra o arquivo.
     * 
     *  @param string $name Nome do arquivo
     *  @param string $mode Modo de abertura
     */
    public function __construct($name = null, $mode = "r") {
        $this->path = dirname($name);
        $this->filename = basename($name);
        $this->mode = $mode;
        $this->file = fopen($this->getPath(), $this->mode);
    }
    /**
     *  Destrutor.
     *  Fecha o ponteiro aberto para leitura do arquivo.
     *
     *  @return boolean Verdadeiro para fechamento do ponteiro
     */
    public function __destruct() {
        return fclose($this->file);
    }
    /**
     *  Escreve o conteudo repassado no arquivo.
     * 
     *  @param string $content Conteudo a ser escrito
     *  @return int 
     */
    public function write($content = "") {
        return fwrite($this->file, $content);
    }
    /**
     *  Lê o arquivo de acordo com o tamanho em bytes solicitado.
     * 
     *  @param int $length Tamanho em bytes do conteudo que se deseja ler
     *  @return string Conteudo do arquivo
     */
    public function read($length = null) {
        if(is_null($length)):
            $length = $this->size() + 1;
        endif;
        return fread($this->file, $length);
    }
    /**
     *  Pega o tamanho em bytes do arquivo.
     * 
     *  @return int Tamanho em bytes do arquivo
     */
    public function size() {
        return filesize($this->getPath());
    }
    /**
     *  Recarrega a posição do ponteiro do arquivo.
     * 
     *  @return boolean Verdadeiro para recarregado
     */
    public function rewind() {
        return rewind($this->file);
    }
    /**
     *  Lê uma linha do arquivo de acordo com o tamanho em bytes solicitado.
     * 
     *  @param int $length Tamanho em bytes do conteudo que se deseja ler
     *  @return string
     */
    public function gets($length = null) {
        return fgets($this->file, $length);
    }
    /**
     *  Verifica se o ponteiro chegou no final do arquivo.
     * 
     *  @return boolean Verdadeiro para o final do arquivo
     */
    public function eof() {
        return feof($this->file);
    }
    /**
     *  Renomeia o arquivo para o nome repassado.
     * 
     *  @param string $name Nome a ser setado para o aquivo
     *  @return boolean Verdadeiro para arquivo renomeado
     */
    public function rename($name = null) {
        fclose($this->file);
        if(rename($this->getPath(), $name)):
            $this->file = fopen($name, $this->mode);
            return true;
        endif;
        return false;
    }
    /**
     *  Pega o path completo do arquivo utilizado.
     * 
     *  @return string Retorna o path completo do arquivo
     */
    public function getPath() {
        return self::path("{$this->path}/{$this->filename}");
    }
    /**
     *  Verifica se o arquivo existe localmente.
     * 
     *  @param string $path Localização do arquivo
     *  @return boolean Verdadeiro para a existência do arquivo
     */
    static public function exists($path = null) {
        return file_exists(self::path($path));
    }
    /**
     *  Pega a localização do arquivo com base na raiz do projeto.
     * 
     *  @param string $filename Nome do arquivo
     *  @return string Localização do arquivo
     */
    static public function path($filename = "") {
        return ROOT . DS . $filename;
    }
}

?>