<?php

require dirname(dirname(__FILE__)) . '/config/bootstrap.php';

$dispatcher = new Dispatcher;
$dispatcher->dispatch();