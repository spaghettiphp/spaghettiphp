<?php

class AssetsHelper extends Helper {
    protected static $assets = array(
        'image' => '/images/',
        'style' => '/styles/',
        'script' => '/scripts/'
    );
    
    public static function addAsset($name, $path) {
        self::$assets[$name] = $path;
    }
    
    public static function removeAsset($name) {
        unset(self::$assets[$name]);
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
    
    public function asset($url, $name, $extension = null) {
        if(!Mapper::isExternal($url)) {
            if(!is_null($extension)) {
                $url = $this->extension($url, $extension);
            }
            
            $url = Mapper::url($url, false, self::$assets[$name]);
        }
        
        return $url;
    }
    
    public function extension($file, $ext) {
        if(strpos($file, '?') === false && Filesystem::extension($file) != $ext) {
            $file .= '.' . $ext;
        }
        
        return $file;
    }
}