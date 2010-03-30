<?php

Config::write('App.defaultExtension', 'htm');
Config::write('App.environment', 'development');
Config::write('App.encoding', 'utf-8');
Config::write('App.rewriteUrl', true);
Config::write('Security.salt', '37b1ffe6afe7577a90f1ac2098605d5711fdc59f');

require 'config/environments/' . Config::read('App.environment') . '.php';
require 'config/connections.php';