<?php

class Error extends Object {
    public function __construct($type = '', $details = array()) {
        $view = new View;
        $filename = Inflector::underscore($type);
        $viewFile = App::path('View', 'errors/' . $filename . '.htm');
        if(!$viewFile):
            $viewFile = App::path('View', 'errors/missing_error.htm');
            $details = array('error' => $type);
        endif;
        $content = $view->renderView($viewFile, array('details' => $details));
        echo $view->renderLayout($content, 'error', 'htm');
        $this->stop();
    }
}