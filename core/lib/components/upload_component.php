<?php
/**
 *  UploadComponent facilita a tarefa de enviar arquivos do cliente para o servidor,
 *  provendo funções para mover e apagar o arquivo, validação, controle de erros,
 *  entre outros.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class UploadComponent extends Component {
    /**
     *  Tipos de arquivo permitidos, vazio para permitir qualquer arquivo.
     */
    public $allowedTypes = array();
    /**
     *  Tamanho máximo permitido (em MB).
     */
    public $maxSize = 2;
    /**
     *  Caminho padrão dos arquivos enviados a partir de /webroot
     */
    public $path = "/";
    /**
     *  Arquivos enviados pelo cliente.
     */
    public $files = array();
    /**
     *  Erros gerados durante o upload.
     */
    public $errors = array();
    
    /**
     *  Inicializa o componente, padronizando o componente de $_FILES.
     *
     *  @param object $controller Objeto controller
     *  @return void
     */
    public function initialize(&$controller) {
        foreach($_FILES as $file => $content):
            if(is_array($content["name"])):
                foreach($content["name"] as $name => $value):
                    if($content["name"][$name] == "") continue;
                    $this->files[$file][$name] = array(
                        "name" => $content["name"][$name],
                        "type" => $content["type"][$name],
                        "tmp_name" => $content["tmp_name"][$name],
                        "error" => $content["error"][$name],
                        "size" => $content["size"][$name]
                    );
                endforeach;
            else:
                $this->files[$file] = $content;
            endif;
        endforeach;
    }
    /**
     *  Valida determinado arquivo.
     *
     *  @param array $file Arquivo a ser validado
     *  @return boolean Verdadeiro quando o arquivo é válido
     */
    public function validates($file = array()) {
        if(empty($file) && !isset($file["name"])):
            return $this->error("InvalidParam");
        endif;
        if($file["size"] > $this->maxSize * 1024 * 1024):
            return $this->error("FileSizeExceeded");
        endif;
        if(!empty($this->allowedTypes) && !in_array($this->ext($file["name"]), $this->allowedTypes)):
            return $this->error("FileTypeNotAllowed");
        endif;
        if($uploadError = $this->UploadError($file["error"])):
            return $this->error($uploadError);
        endif;
        return true;
    }
    /**
     *  Move um arquivo enviado pelo cliente para determinado local na aplicação,
     *  fazendo as validações necessárias.
     *
     *  @param array $file Arquivo a ser movido
     *  @param string $path Caminho para enviar o arquivo
     *  @param string $name Novo nome do arquivo
     *  @return boolean Verdadeiro se o arquivo foi movido
     */
    public function upload($file = array(), $path = null, $name = null) {
        $path = is_null($path) ? $this->path : $path;
        $name = is_null($name) ? $file["name"] : $name;
        if($this->validates($file)):
            $path = APP . DS . "webroot" . $path;
            if(!is_dir($path)):
                mkdir($path, 0777, true);
            endif;
            if(move_uploaded_file($file["tmp_name"], $path . DS . $name)):
                return true;
            else:
                return $this->error("CantMoveFile");
            endif;
        else:
            return false;
        endif;
    }
    /**
     *  Apaga um arquivo.
     *
     *  @param string $filename Nome do arquivo a ser apagado
     *  @param string $path Caminho onde reside o arquivo
     *  @return boolean Verdadeiro se o arquivo foi apagado.
     */
    public function delete($filename = "", $path = null) {
        $path = is_null($path) ? $this->path : $path;
        $file = APP . $path . DS . $filename;
        if(file_exists($file)):
            if(unlink($file)):
                return true;
            else:
                return $this->error("CantDeleteFile");
            endif;
        else:
            return $this->error("CantFindFile");
        endif;
    }
    /**
     *  Retorna a extensão de um arquivo.
     *
     *  @param string $filename Nome do arquivo
     *  @return string Extensão do arquivo
     */
    public function ext($filename = "") {
        return strtolower(trim(substr($filename, strrpos($filename, ".") + 1, strlen($filename))));
    }
    /**
     *  Adiciona um novo erro ao componente.
     *
     *  @param string $type Mensagem de erro
     *  @param array $details Detalhes do erro
     *  @return false
     */
    public function error($type = "", $details = array()) {
        $this->errors []= $type;
        return false;
    }
    /**
     *  Limpa os erros gerados pelo componente.
     *
     *  @return true
     */
    public function clear() {
        $this->errors = array();
        return true;
    }
    /**
     *  Reconhece erros de upload através de $_FILES.
     *
     *  @param int $error Código de erro
     *  @return mixed Mensagem de erro, ou falso caso não hajam erros.
     */
    public function uploadError($error = 0) {
        $message = false;
        switch($error):
            case UPLOAD_ERR_OK: break;
            case UPLOAD_ERR_INI_SIZE: $message = "IniFileSizeExceeded"; break;
            case UPLOAD_ERR_FORM_SIZE: $message = "FormFileSizeExceeded"; break;
            case UPLOAD_ERR_PARTIAL: $message = "PartiallyUploaded"; break;
            case UPLOAD_ERR_NO_FILE: $message = "NoFile"; break;
            case UPLOAD_ERR_NO_TMP_DIR: $message = "MissingTempDir"; break;
            case UPLOAD_ERR_CANT_WRITE: $message = "CantWriteFile"; break;
            default: $message = "UnknownFileError";
        endswitch;
        return $message;
    }
}

?>