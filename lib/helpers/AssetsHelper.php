<?php

class AssetsHelper extends Helper {
    protected static $assets = array(
        'image' => '/images/',
        'style' => '/styles/',
        'script' => '/scripts/'
    );
    
    public function __construct($view) {
        parent::__construct($view);
    }
    public function image($url) {
        return $this->asset($url, 'image');
    }
    public function script($url) {
        return $this->asset($url, 'script', 'js');
    }
    public function stylesheet($url) {
        return $this->asset($url, 'style', 'css');
    }
    public function asset($url, $type, $extension = null) {
        if(!$this->external($url)):
            if(!is_null($extension)):
                $url = $this->extension($url, $extension);
            endif;
            $url = Mapper::url(self::$assets[$type] . $url);
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