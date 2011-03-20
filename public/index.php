<?php

try {
    require dirname(dirname(__FILE__)) . '/config/bootstrap.php';
    require 'config/settings.php';
    require 'config/connections.php';
    require 'config/routes.php';

    echo Dispatcher::dispatch();
}
catch(Exception $e) {
    Debug::log((string) $e);
    
    if(Config::read('Debug.showErrors')) {
        echo '<pre>', $e, '</pre>';
    }
    else {
        // @todo do something to prevent white screen of death
    }
}