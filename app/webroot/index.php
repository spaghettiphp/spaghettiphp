<?php

$date = microtime(true);
require dirname(dirname(dirname(__FILE__))) . '/config/bootstrap.php';

$dispatcher = new Dispatcher;
$dispatcher->dispatch();
pr(microtime(true) - $date);
