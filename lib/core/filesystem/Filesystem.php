<?php
class Filesystem extends Object{
    public static $basepath = '/public/';
    public static $rewrite = array(
        'Gb'    => 1073741824,
        'Mb'    => 1048576,
        'Kb'    => 1024,
        'bytes' => 1,
    );

    public static function read($file) {
        $file = self::fullPath($file);
        if(self::exists($file)):
            return file_get_contents($file);
        else:
            return null;
        endif;
    }
    public static function write($file, $content = '', $append = false) {
        $file = self::fullPath($file);
        if(!$append):
            file_put_contents($file, $content);
        elseif($append == 'append'):
            file_put_contents($file, $content, FILE_APPEND);
        elseif($append = 'prepend'):
            file_put_contents($file, $content . self::read($file));
        endif;
        return true;
    }
    public static function getFiles($path = '', $pattern = '*') {
        $path = self::fullPath($path);
        return glob($path . $pattern);
    }
    public static function size($file, $rewriteSize = true) {
        $size = filesize(self::fullPath($file));
        if($rewriteSize):
            foreach(self::$rewrite as $key => $value):
                if($size >= $value):
                    return number_format($size/$value, 2) . ' ' . $key;
                endif;
            endforeach;
        else:
            return $size;
        endif;
    }
    public static function copy($file, $destinationDir, $deleteOriginal = false) {
        if(self::exists($file)):
            if(copy(self::fullPath($file), self::fullPath($destinationDir) . DIRECTORY_SEPARATOR . basename($file))):
                if($deleteOriginal):
                    return self::delete($file);
                endif;
                return true;
            endif;
        else:
            return false;
        endif;
    }
    public static function isUploaded($file) {
        return is_uploaded_file(self::fullPath($file));
    }
    public static function delete($file, $deleteIfNotEmpty = true) {
        if (!self::exists($file)) return false;
        $file = self::fullPath($file);
        //não é diretório
        if(!is_dir($file)):
            return unlink($file);
        //é diretório
        else:
            $dir = rtrim($file, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $files = self::getFiles($dir);
            //é um diretório vazio
            if(count($files) == 0):
                return rmdir($dir);
            //é um diretório cheio
            else:
                if($deleteIfNotEmpty === false) return true;
                
                foreach($files as $each):
                    self::delete($each);
                endforeach;
                return self::delete($dir);
            endif;
        endif;
    }
    public static function create($dir, $mode = 0655) {
        $dir = self::fullPath($dir);
        if(!self::exists($dir)) return mkdir($dir, $mode, true);
    }
    public static function rename($file, $newName) {
        $file = self::fullPath($file);
        if(self::exists($file))
            return rename($file, dirname($file) . DIRECTORY_SEPARATOR . $newName);
        return false;
    }
    public static function exists($file) {
        return file_exists(self::fullPath($file));
    }
    public static function hasPermission($file, $permissionNeeded = array('execute', 'read', 'write')) {
        //Estende o self::exists(), verificando se
        //o usuário atual tem permissão para a operação
        $file = self::fullPath($file);
        return true;
    }
    public static function extension($file) {
        return end(explode('.', $file));
    }
    public static function fullPath($path, $absolute = true) {
        //Se já tá com o path completo, porém relativo ao spaghetti
        if(strpos($path, SPAGHETTI_ROOT) === 0) return $path;
        
        $path = $absolute ?
            SPAGHETTI_ROOT . DIRECTORY_SEPARATOR .
                self::$basepath . DIRECTORY_SEPARATOR . $path : $path;
                
        return preg_replace('([/\\\]+)', DIRECTORY_SEPARATOR, $path);
    }
}
?>