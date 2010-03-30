<?php

Connection::add(array(
    'development' => array(
        'driver' => 'Mysql',
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'test',
        'prefix' => ''
    ),
    'production' => array(
        'driver' => 'Mysql',
        'host' => '',
        'user' => '',
        'password' => '',
        'database' => '',
        'prefix' => ''
    )
));