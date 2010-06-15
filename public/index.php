<?php

require dirname(dirname(__FILE__)) . '/config/bootstrap.php';

try {
    echo Dispatcher::dispatch();
    throw new Exception("holy cow!");
}
catch(Exception $e) {
    echo new SpaghettiException($e);
}
