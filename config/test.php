<?php

require dirname(__FILE__) . '/bootstrap.php';
require 'config/settings.php';
Config::write('App.environment', 'test');
require 'config/connections.php';
