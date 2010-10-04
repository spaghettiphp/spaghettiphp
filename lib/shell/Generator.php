<?php

class Generator {
    public static function exists($type) {
        $type = Inflector::underscore($type);
        $class = Inflector::camelize($type) . 'Generator';
        $path = 'lib/generators/' . $type;
        $file = $path . '/' . $class . '.php';
        
        if(Filesystem::exists($file)):
            require_once $file;
            return $class;
        endif;
        
        return false;
    }
    
    public static function invoke($type, $args) {
        if($class = self::exists($type)):
            $generator = new $class;
        else:
            die('could not find generator ' . $type);
        endif;

        if(empty($args)):
            self::printUsage($type);
            return false;
        else:
            return call_user_func_array(array($generator, 'start'), $args);
        endif;
    }
    
    public static function printUsage($type) {
        $type = Inflector::underscore($type);
        echo Filesystem::read('/lib/generators/' . $type . '/USAGE');
    }
    
    public function renderTemplate($source, $destination, $data = array()) {
        if(!Filesystem::exists($destination)):
            $view = new View();
            $content = $view->renderView(Filesystem::path($source), $data);
            Filesystem::write($destination, $content);

            $this->log('created', $destination);
        else:
            $this->log('exists', $destination);
        endif;
    }
    
    public function createDir($destination) {
        if(Filesystem::isDir($destination)):
            $this->log('exists', $destination);
        else:
            Filesystem::createDir($destination, 0777);
            $this->log('created', $destination);
        endif;
    }
    
    public function log($status, $message) {
        printf('%12s  %s' . PHP_EOL, $status, $message);
    }
}