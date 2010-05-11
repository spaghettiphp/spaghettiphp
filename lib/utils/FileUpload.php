<?php

class FileUpload {
    public $allowedTypes = array();
    public $maxSize = false;
    public $path = '/';
    public $files = array();
    public $errors = array();
    
    public function __construct($files = null) {
        if(is_null($files)):
            $this->files = $_FILES;
        else:
            $this->files = $files;
        endif;
    }
    public function upload($file, $name = null, $path = '') {
        $path = Filesystem::path('public/' . $path);
        if(is_null($name)):
            $name = $file['name'];
        endif;

        if($this->validates($file)):
            if(!is_dir($path)):
                Filesystem::createDir($path);
            endif;
            if(move_uploaded_file($file['tmp_name'], $path . '/' . $name)):
                return true;
            else:
                return $this->error('CantMoveFile');
            endif;
        endif;

        return false;
    }
    public function validates($file) {
        $this->errors = array();
        
        if(empty($file) && !isset($file['name'])):
            return $this->error('InvalidParam');
        endif;

        if($this->maxSize && filesize($file['tmp_name']) > $this->maxSize * 1024 * 1024):
            return $this->error('FileSizeExceeded');
        endif;

        $ext = Filesystem::extension($file['name']);
        if(!empty($this->allowedTypes) && !in_array($ext, $this->allowedTypes)):
            return $this->error('FileTypeNotAllowed');
        endif;

        if($uploadError = $this->uploadError($file['error'])):
            return $this->error($uploadError);
        endif;

        return true;
    }
    public function error($type, $details = array()) {
        $this->errors []= $type;
        return false;
    }
    protected function uploadError($error = 0) {
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