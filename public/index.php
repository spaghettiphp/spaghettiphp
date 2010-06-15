<?php

require dirname(dirname(__FILE__)) . '/config/bootstrap.php';

try {
    echo Dispatcher::dispatch();
}
catch(Exception $e) {
    echo new SpaghettiException($e);
}
