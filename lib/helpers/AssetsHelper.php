<?php

class AssetsHelper extends Helper {
    public function __construct($view) {
        parent::__construct($view);
    }
    public function image($url) {
        if(!$this->external($url)):
            $src = Mapper::url('/images/' . $url);
        endif;
        
        return $url;
    }
    public function script($url) {
        if(!$this->external($url)):
            $url = Mapper::url('/scripts/' . $this->extension($url, 'js'));
        endif;
        
        return $url;
    }
    public function stylesheet($url) {
        if(!$this->external($url)):
            $url = Mapper::url('/styles/' . $this->extension($url, 'css'));
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