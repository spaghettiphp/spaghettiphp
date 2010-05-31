<?php

require dirname(dirname(__FILE__)) . '/config/bootstrap.php';

try {
    echo Dispatcher::dispatch();
}
catch(Exception $e) {
    $exception = new SpaghettiException(get_class($e), $e->getMessage(), 500);
    $exception->setFile($e->getFile());
    $exception->trace = $e->__toString();
    echo $exception;
}
