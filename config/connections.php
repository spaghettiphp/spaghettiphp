<?php

Connection::add('default', array(
    'development' => array(
        'driver' => 'mysql',
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'app_name',
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