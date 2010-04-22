<?php

class Error extends Object {
    public function __construct($type, $details = array()) {
        $view = new View;
        $view->layout = 'error';
        $filename = Inflector::underscore($type);
        if(!Loader::exists('View', 'errors/' . $filename . '.htm')):
            $filename = 'missing_error';
            $details = array('error' => $type);
        endif;
        echo $view->render('errors/' . $filename, array('details' => $details));
        $this->stop();
    }
}