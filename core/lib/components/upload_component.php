<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class UploadComponent extends Object {
    public $allowedTypes = array("jpg", "jpeg", "gif", "png");
    public $maxSize = 2; /* MB */
    public $path = "/";
    public $files = array();
    public $errors = array();
    public function initialize() {
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
    public function validates($file = array()) {
        if($file["size"] > $this->maxSize * 1024 * 1024):
            return $this->error("FileSizeExceeded");
        endif;
        if(!in_array($this->ext($file["name"]), $this->allowedTypes)):
            return $this->error("FileTypeNotAllowed");
        endif;
        if($uploadError = $this->UploadError($file["error"])):
            return $this->error($uploadError);
        endif;
        return true;
    }
    public function upload($file = array(), $path = null, $name = null) {
        $path = is_null($path) ? $this->path : $path;
        $name = is_null($name) ? $file["name"] : $name;
        if($this->validates($file)):
            $path = APP . $path;
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
    public function ext($filename = "") {
        return trim(substr($filename, strrpos($filename, ".") + 1, strlen($filename)));
    }
    public function error($message = "") {
        $this->errors []= $message;
        return false;
    }
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