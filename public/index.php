<?php

try {
    require dirname(dirname(__FILE__)) . '/config/bootstrap.php';
    echo Dispatcher::dispatch();
}
catch(Exception $e) {
    if(!($e instanceof SpaghettiException)):
        $e = new SpaghettiException($e->getMessage(), $e->getCode());
    endif;
    echo $e;
}