<?php

class PhpErrorException extends Exception {
    public function __construct($message, $code, $file, $line, $context) {
        parent::__construct($message, $code);
    }
}

class SpaghettiException {
    protected $exception;
    
    public function __construct($exception) {
        $this->exception = $exception;
    }
    public function __toString() {
        ob_end_clean();
        if(Filesystem::exists('app/views/layouts/error.htm.php')):
            $view = new View();
            return  $view->renderView('app/views/layouts/error.htm.php', array('exception' => $this->exception));
        else:
            echo '<pre>';
            throw new Exception('error layout was not found');
        endif;
    }
}