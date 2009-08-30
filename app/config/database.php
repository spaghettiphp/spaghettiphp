<?php
/**
 * Aqui você deve definir suas configurações de banco de dados. Essas são as únicas
 * configurações necessárias para rodar uma aplicação do Spaghetti.
 * 
 */

$database = array(
    "development" => array(
        "host" => "localhost",
        "user" => "root",
        "password" => "",
        "database" => "tests",
        "prefix" => ""
    )
);

/**
 * A linha seguinte serve apenas para definir o banco de dados de acordo com o
 * ambiente em que a aplicação está rodando.    
 */
Config::write("database", $database[Config::read("environment")]);

?>