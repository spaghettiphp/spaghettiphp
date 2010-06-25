<?php

try {
    require dirname(dirname(__FILE__)) . '/config/bootstrap.php';
    echo Dispatcher::dispatch();
}
catch(Exception $e) {
    if(!($e instanceof SpaghettiException)):
        $e = new SpaghettiException('Uncaught Exception', $e->getCode(), $e->getMessage(), $e);
    endif;
    echo $e->toString();
}