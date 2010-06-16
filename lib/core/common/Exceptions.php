<?php

class PhpErrorException extends Exception {
    public function __construct($message, $code, $file, $line) {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
    }
}

class SpaghettiException extends Exception {
    protected $exception;
    protected $status;
    
    public function __construct($exception, $status = 500) {
        $this->exception = $exception;
        $this->status = $status;
    }
    function header($status) {
        $codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out'
        );
        header('HTTP/1.1 ' . $status . ' ' . $codes[$status]);
    }
    public function __toString() {
        ob_end_clean();
        $this->header($this->status);
        if(Filesystem::exists('app/views/layouts/error.htm.php')):
            $view = new View();
            return  $view->renderView('app/views/layouts/error.htm.php', array(
                'exception' => $this->exception
            ));
        else:
            echo '<pre>';
            throw new Exception('error layout was not found');
        endif;
    }
}

class MissingControllerException extends Exception {}
class MissingActionException extends Exception {}
class MissingComponentException extends Exception {}
class MissingTableException extends Exception {}
class MissingViewException extends Exception {}
class MissingLayoutException extends Exception {}