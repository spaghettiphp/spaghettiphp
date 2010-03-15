<?php

Config::write('database', array(
    'development' => array(
        'driver' => 'mysql',
        'host' => '',
        'user' => '',
        'password' => '',
        'database' => '',
        'prefix' => ''
    ),
    'production' => array(
        'driver' => '',
        'host' => '',
        'user' => '',
        'password' => '',
        'database' => '',
        'prefix' => ''
    )
));