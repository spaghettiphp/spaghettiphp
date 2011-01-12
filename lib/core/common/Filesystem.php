<?php

class Filesystem{
    public static $rewrite = array(
        'Gb' => 1073741824,
        'Mb' => 1048576,
        'Kb' => 1024,
        'bytes' => 1
    );

    public static function read($file) {
        $file = self::path($file);
        if(self::exists($file)):
            return file_get_contents($file);
        else:
            return null;
        endif;
    }
    public static function write($file, $content = '', $append = false) {
        $file = self::path($file);
        switch($append):
            case 'append':
                return file_put_contents($file, $content, FILE_APPEND);
            case 'prepend':
                return file_put_contents($file, $content . self::read($file));
            default:
                return file_put_contents($file, $content);
        endswitch;
    }
    public static function getFiles($path = '', $pattern = '*') {
        $path = self::path($path);
        return array_slice(scandir($path), 2);
    }
    public static function size($file, $rewrite = true) {
        if(!self::exists($file)):
            return false;
        endif;

        $size = filesize(self::path($file));

        if($rewrite):
            foreach(self::$rewrite as $key => $value):
                if($size >= $value):
                    return number_format($size / $value, 2) . ' ' . $key;
                endif;
            endforeach;
        else:
            return $size;
        endif;
    }
    public static function copy($file, $destination) {
        if(self::exists($file)):
            $destination = self::path($destination) . '/' . basename($file);
            return copy(self::path($file), $destination);
        endif;
        return false;
    }
    public static function isDir($path) {
        return is_dir(self::path($path));
    }
    public static function isUploaded($file) {
        return is_uploaded_file(self::path($file));
    }
    public static function delete($file, $deleteIfNotEmpty = true) {
        if (!self::exists($file)):
            return false;
        endif;
        $file = self::path($file);
        
        if(!self::isDir($file)):
            return unlink($file);
        else:
            $dir = rtrim($file, DIRECTORY_SEPARATOR) . '/';
            $files = self::getFiles($dir);
        
            if(!count($files)):
                return rmdir($dir);
            else:
                if(!$deleteIfNotEmpty):
                    return true;
                endif;
                
                foreach($files as $each):
                    self::delete($each);
                endforeach;
                
                return self::delete($dir);
            endif;
        endif;
    }
    public static function createDir($dir, $mode = 0644) {
        $dir = self::path($dir);
        if(!self::exists($dir)):
            return mkdir($dir, $mode, true);
        endif;
    }
    public static function rename($file, $newName) {
        $file = self::path($file);
        if(self::exists($file)):
            return rename($file, dirname($file) . '/' . $newName);
        endif;
        return false;
    }
    public static function exists($file) {
        return file_exists(self::path($file));
    }
    public static function hasPermission($file, $permission = array('execute', 'read', 'write')) {
        $file = self::path($file);
        $functions = array(
            'execute' => 'is_executable',
            'read' => 'is_readable',
            'write' => 'is_writeable',
        );

        foreach($permission as $action):
            if(!$functions[$action]($file)):
                return false;
            endif;
        endforeach;

        return true;
    }
    public static function extension($file) {
        $explode = explode('.', $file);
        if(($count = count($explode)) > 1):
            return strtolower($explode[$count - 1]);
        endif;
        return null;
    }
    public static function path($path, $returnAbsolute = true) {
         if(strpos($path, SPAGHETTI_ROOT) === false && !preg_match('(^[a-z]+:)i', $path, $out)):
            if($returnAbsolute):
                $path = SPAGHETTI_ROOT . '/' . $path;
            endif;
        endif;

        $pattern = '(([^:])[/\\\]+|\\\)'; // v.4.3    
        return preg_replace($pattern, '$1/', $path);
    }
}