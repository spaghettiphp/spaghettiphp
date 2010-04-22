<?php

class AssetsHelper extends Helper {
    public function __construct($view) {
        parent::__construct($view);
    }
    public function image($url) {
        return $this->asset($url, 'images');
    }
    public function script($url) {
        return $this->asset($url, 'scripts', 'js');
    }
    public function stylesheet($url) {
        return $this->asset($url, 'styles', 'css');
    }
    protected function asset($url, $type, $extension = null) {
        if(!$this->external($url)):
            if(!is_null($extension)):
                $url = $this->extension($url, $extension);
            endif;
            $url = Mapper::url('/' . $type . '/' . $url);
        endif;
        
        return $url;
    }
    public function external($url) {
        return preg_match('/^[a-z]+:/', $url);
    }
    public function extension($file, $extension) {
        if(strpos($file, '?') === false):
            if(strpos($file, '.' . $extension) === false):
                $file .= '.' . $extension;
            endif;
        endif;
        
        return $file;
    }
}