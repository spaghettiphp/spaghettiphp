<?php

require dirname(dirname(__FILE__)) . '/config/bootstrap.php';
require 'lib/shell/Generator.php';

$filename = array_shift($argv);
$type = array_shift($argv);
Generator::invoke($type, $argv);