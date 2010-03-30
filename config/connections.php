<?php

Connection::add(array(
    'development' => array(
        'driver' => 'mysql',
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'microblog',
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