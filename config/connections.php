<?php

Connection::add(array(
    'development' => array(
        'driver' => 'MySql',
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'spaghetti',
        'prefix' => ''
    ),

    //*
    'sqlite_dev' => array(
        'driver' => 'SQLite',
        'path' => '/path/to/database.sqlite', //relative to SPAGHETTI_ROOT
        'database' => 'data',
        'prefix' => ''
    ),/**/
    
    //*
    'postgres_dev' => array(
        'driver' => 'PostgreSql',
        'host' => 'localhost',
        'user' => 'postgres',
        'password' => 'password',
        'database' => 'database',
        'port' => 5432,
        'prefix' => ''
    ), /**/


    'production' => array(
        'driver' => 'MySql',
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'spaghetti',
        'prefix' => ''
    ),
    'test' => array(
        'driver' => 'MySql',
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'spaghetti',
        'prefix' => ''
    )
));

$env = Config::read('App.environment');
Connection::add('default', Connection::getConfig($env));
