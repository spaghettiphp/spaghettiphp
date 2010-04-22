<?php

require_once 'lib/core/filesystem/Filesystem.php';

class FileUpload {
    public $allowedTypes = array();
    public $maxSize = 2;
    public $path = '/';
    public $files = array();
    public $errors = array();
    
    public function validates($file = array()) {
        if(empty($file) && !isset($file['name'])):
            return $this->error('InvalidParam');
        endif;
        if($file['size'] > $this->maxSize * 1024 * 1024):
            return $this->error('FileSizeExceeded');
        endif;
        if(!empty($this->allowedTypes) && !in_array(Filesystem::extension($file['name']), $this->allowedTypes)):
            return $this->error('FileTypeNotAllowed');
        endif;
        if($uploadError = $this->uploadError($file['error'])):
            return $this->error($uploadError);
        endif;
        return true;
    }
    public function uploadFile($file = array(), $path = null, $name = null) {
        $path = is_null($path) ? $this->path : $path;
        $name = is_null($name) ? $file['name'] : $name;
        if($this->validates($file)):
            $path = SPAGHETTI_ROOT . '/public' . $path;
            if(!is_dir($path)):
                mkdir($path, 0777, true);
            endif;
            if(move_uploaded_file($file['tmp_name'], $path . '/' . $name)):
                return true;
            else:
                return $this->error('CantMoveFile');
            endif;
        else:
            return false;
        endif;
    }
    public function error($type, $details = array()) {
        $this->errors []= $type;
        return false;
    }
    public function clear() {
        $this->errors = array();
        return true;
    }
    public function uploadError($error = 0) {
        switch($error):
            case UPLOAD_ERR_OK:
                return false;
            case UPLOAD_ERR_INI_SIZE:
                return 'IniFileSizeExceeded';
            case UPLOAD_ERR_FORM_SIZE:
                return 'FormFileSizeExceeded';
            case UPLOAD_ERR_PARTIAL:
                return 'PartiallyUploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'NoFile';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'MissingTempDir';
            case UPLOAD_ERR_CANT_WRITE:
                return 'CantWriteFile';
            default:
                return 'UnknownFileError';
        endswitch;
    }
}