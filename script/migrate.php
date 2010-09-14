<?php

require dirname(dirname(__FILE__)) . '/config/bootstrap.php';
require 'config/settings.php';
require 'config/connections.php';

function get_migrations() {
    $migrations = Filesystem::getFiles('db/migrations');
    natsort($migrations);
    return $migrations;
}

function create_schema_migrations($connection) {
    $sql = <<<EOT
CREATE TABLE `schema_migrations` (
  `version` varchar(255),
  PRIMARY KEY (`version`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;    
EOT;
    $connection->query($sql);
}

function should_migrate($migration, $connection) {
    $version = substr($migration, 0, 14);
    $should_migrate = $connection->count(array(
        'table' => 'schema_migrations',
        'conditions' => array(
            'version' => $version
        )
    ));
    return !$should_migrate;
}

function migrate($migration, $connection) {
    echo 'importing ' . $migration . '... ';
    $connection->query(utf8_decode(Filesystem::read('db/migrations/' . $migration)));
    $connection->create(array(
        'table' => 'schema_migrations',
        'values' => array(
            'version' => substr($migration, 0, 14)
        )
    ));
    echo 'done' . PHP_EOL;
}

$connection = Connection::get(Config::read('App.environment'));
if(!in_array('schema_migrations', $connection->listSources())):
    create_schema_migrations($connection);
endif;

$migrations = get_migrations();
foreach($migrations as $migration):
    if(should_migrate($migration, $connection)):
        migrate($migration, $connection);
    endif;
endforeach;