<?php
/**
 *  Put Description here
 * 
 */

$database = array(
    "development" => array(
        "host" => "localhost",
        "user" => "root",
        "password" => "",
        "database" => "test_spg",
        "prefix" => ""
    )
);

Config::write("database", $database[Config::read("environment")]);

?>