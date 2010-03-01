<?php
/**
 * Aqui você deve definir suas configurações de banco de dados, todas de acordo
 * com um determinado ambiente de desenvolvimento. Você pode definir quantos
 * ambientes forem necessários.
 * 
 */

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