<?php

Connection::add(array(
    'development' => array(
        'driver' => 'MySql',
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'test',
        'prefix' => ''
    ),
    'production' => array(
        'driver' => 'MySql',
        'host' => '',
        'user' => '',
        'password' => '',
        'database' => '',
        'prefix' => ''
    )
));