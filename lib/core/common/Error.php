<?php

class Error {
    public function __construct($type, $details = array()) {
        $filename = Inflector::underscore($type);
        if(!Loader::exists('View', 'errors/' . $filename . '.htm')):
            $filename = 'missing_error';
            $details = array('error' => $type);
        endif;
        $view = new View;
        echo $view->render('errors/' . $filename, array('details' => $details), 'error');
        exit(0);
    }
}