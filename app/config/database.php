<?php
/**
 * Aqui você deve definir suas configurações de banco de dados, todas de acordo
 * com um determinado ambiente de desenvolvimento. Você pode definir quantos
 * ambientes quantos forem necessários. Essas são as únicas configurações
 * necessárias para rodar uma aplicação com o Spaghetti.
 * 
 */

Config::write("database", array(
    "development" => array(
        "driver" => "mysql",
        "host" => "host",
        "user" => "username",
        "password" => "password",
        "database" => "app",
        "prefix" => ""
    ),
    "production" => array(
        "driver" => "mysql",
        "host" => "host",
        "user" => "username",
        "password" => "password",
        "database" => "app",
        "prefix" => ""
    )
));

?>