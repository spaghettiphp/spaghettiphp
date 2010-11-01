<?php

try {
    require dirname(dirname(__FILE__)) . '/config/bootstrap.php';
    require 'config/settings.php';
    require 'config/connections.php';
    require 'config/routes.php';

    echo Dispatcher::dispatch();
}
catch(Exception $e) {
    if(!($e instanceof SpaghettiException)) {
        $e = new SpaghettiException('Uncaught Exception', $e->getCode(), $e->getMessage());
    }

    echo $e->toString();
}