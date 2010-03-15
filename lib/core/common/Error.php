<?php

class Error extends Object {
    public function __construct($type = '', $details = array()) {
        $view = new View;
        $filename = Inflector::underscore($type);
        if(!Loader::exists('View', 'errors/' . $filename . '.htm')):
            $filename = 'missing_error.htm';
            $details = array('error' => $type);
        endif;
        $viewFile = Loader::path('View', 'errors/' . $filename . '.htm');
        $content = $view->renderView($viewFile, array('details' => $details));
        echo $view->renderLayout($content, 'error', 'htm');
        $this->stop();
    }
}