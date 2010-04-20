<?php

class Generator extends Object {
    public static function exists($type) {
        $type = Inflector::underscore($type);
        $class = Inflector::camelize($type) . 'Generator';
        $path = SPAGHETTI_ROOT . '/lib/generators/' . $type;
        $file = $path . '/' . $class . '.php';
        if(file_exists($file)):
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
            return call_user_func_array(array(&$generator, 'start'), $args);
        endif;
    }
    public static function printUsage($type) {
        $type = Inflector::underscore($type);
        $usage_file = SPAGHETTI_ROOT . '/lib/generators/' . $type . '/USAGE';
        echo file_get_contents($usage_file);
    }
    public function renderTemplate($source, $destination, $data = array()) {
        $app_destination = SPAGHETTI_ROOT . '/' . $destination;
        if(!file_exists($app_destination)):
            $view = new View();
            $content = $view->renderView($source, $data);
            file_put_contents($app_destination, $content);

            $this->log('created', $destination);
        else:
            $this->log('exists', $destination);
        endif;
    }
    public function createDir($destination) {
        $path = SPAGHETTI_ROOT . '/' . $destination;
        if(is_dir($path)):
            $this->log('exists', $destination);
        else:
            mkdir($path, 0644, true);
            $this->log('created', $destination);
        endif;
    }
    public function log($status, $message) {
        printf('%12s  %s' . PHP_EOL, $status, $message);
    }
}